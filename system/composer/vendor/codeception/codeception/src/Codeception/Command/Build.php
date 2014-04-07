<?php
namespace Codeception\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use \Symfony\Component\Console\Helper\DialogHelper;

class Build extends Base
{

	protected $template = <<<EOF
<?php
// This class was automatically generated by build task
// You should not change it manually as it will be overwritten on next build
// @codingStandardsIgnoreFile

%s
use \Codeception\Maybe;
%s

%s %s extends %s
{
    %s
}


EOF;

    protected $methodTemplate = <<<EOF

    /**
     * This method is generated.
     * Documentation taken from corresponding module.
     * ----------------------------------------------
     *
     %s
     * @see %s::%s()
     * @return \Codeception\Maybe
     */
    public function %s(%s) {
        \$this->scenario->addStep(new \Codeception\Step\%s('%s', func_get_args()));
        if (\$this->scenario->running()) {
            \$result = \$this->scenario->runStep();
            return new Maybe(\$result);
        }
        return new Maybe();
    }
EOF;

    protected $inheritedMethodTemplate = ' * @method void %s(%s)';


    public function getDescription() {
        return 'Generates base classes for all suites';
    }

    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $suites = $this->getSuites($input->getOption('config'));

        $output->writeln("<info>Building Guy classes for suites: ".implode(', ', $suites).'</info>');

        foreach ($suites as $suite) {
            $settings = $this->getSuiteConfig($suite, $input->getOption('config'));
            $namespace = rtrim($settings['namespace'],'\\');
            $modules = \Codeception\Configuration::modules($settings, false);

            $code = array();
            $methodCounter = 0;

            $output->writeln('<info>'.$settings['class_name'] . "</info> includes modules: ".implode(', ',array_keys($modules)));

	        $docblock = array();
            foreach ($modules as $module) {
                $docblock[] = "use ".get_class($module).";";
            }

            $methods = array();
            $actions = \Codeception\Configuration::actions($modules);

            foreach ($actions as $action => $moduleName) {
                if (in_array($action, $methods)) continue;
                $module = $modules[$moduleName];
                $method = new \ReflectionMethod(get_class($module), $action);
                $code[] = $this->addMethod($method);
                $methods[] = $action;
                $methodCounter++;
            }

            $docblock = $this->prependAbstractGuyDocBlocks($docblock);

            $contents = sprintf($this->template,
                $namespace ? "namespace $namespace;" : '',
	            implode("\n", $docblock),
	            'class',
                $settings['class_name'],
	            '\Codeception\AbstractGuy',
	            implode("\n\n ", $code));

            $file = $settings['path'].$this->getClassName($settings['class_name']).'.php';
            $this->save($file, $contents, true);
            $output->writeln("{$settings['class_name']}.php generated successfully. $methodCounter methods added");
        }
    }


    protected function addMethod(\ReflectionMethod $refMethod)
    {
        $class = $refMethod->getDeclaringClass();
        $params = $this->getParamsString($refMethod);
        $module = $class->getName();

        $body = '';
        $doc = $this->addDoc($class, $refMethod);
        $doc = str_replace('/**', '', $doc);
        $doc = trim(str_replace('*/','',$doc));
        if (!$doc) $doc = "*";

        $conditionalDoc = $doc . "\n    * Conditional Assertion: Test won't be stopped on fail";

        if (0 === strpos($refMethod->name, 'see')) {
            $type = 'Assertion';
            $body .= sprintf($this->methodTemplate, $conditionalDoc, $module, $refMethod->name, 'can'.ucfirst($refMethod->name), $params, 'ConditionalAssertion', $refMethod->name);

        } elseif (0 === strpos($refMethod->name, 'dontSee')) {
            $action = str_replace('dont','cant',$refMethod->name);
            $body .= sprintf($this->methodTemplate, $conditionalDoc, $module, $refMethod->name, $action, $params, 'ConditionalAssertion', $refMethod->name);
            $type = 'Assertion';
        } elseif (0 === strpos($refMethod->name, 'am')) {
            $type = 'Condition';
        } else {
            $type = 'Action';
        }
        $body .= sprintf($this->methodTemplate, $doc, $module, $refMethod->name, $refMethod->name, $params, $type, $refMethod->name);

        return $body;
    }

    /**
     * @param \ReflectionMethod $refMethod
     * @return array
     */
    protected function getParamsString(\ReflectionMethod $refMethod)
    {
        $params = array();
        foreach ($refMethod->getParameters() as $param) {

            if ($param->isOptional()) {
                $params[] = '$' . $param->name . ' = null';
            } else {
                $params[] = '$' . $param->name;
            };

        }
        return implode(', ',$params);
    }

    /**
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $refMethod
     * @return string
     */
    protected function addDoc(\ReflectionClass $class, \ReflectionMethod $refMethod)
    {
        $doc = $refMethod->getDocComment();

        if (!$doc) {
            $interfaces = $class->getInterfaces();
            foreach ($interfaces as $interface) {
                $i = new \ReflectionClass($interface->name);
                if ($i->hasMethod($refMethod->name)) {
                    $doc = $i->getMethod($refMethod->name)->getDocComment();
                    break;
                }
            }
        }

        if (!$doc and $class->getParentClass()) {
            $parent = new \ReflectionClass($class->getParentClass()->name);
            if ($parent->hasMethod($refMethod->name)) {
                $doc = $parent->getMethod($refMethod->name)->getDocComment();
                return $doc;
            }
            return $doc;
        }
        return $doc;
    }

    /**
     * @param $aliases
     * @return array
     */
    protected function prependAbstractGuyDocBlocks($aliases)
    {
        $inherited = array();

        $class   = new \ReflectionClass('\Codeception\\AbstractGuy');
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->name == '__call') continue; // skipping magic
            if ($method->name == '__construct') continue; // skipping magic
            $params = $this->getParamsString($method);
            $inherited[] = sprintf($this->inheritedMethodTemplate, $method->name, $params);
        }

        $aliases[] = "\n/**\n * Inherited methods";
        $aliases[] = implode("\n", $inherited);
        $aliases[] = '*/';
        return $aliases;
    }
}
