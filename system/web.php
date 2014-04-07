<?php

// Load system Composer autoloader
if (file_exists(__DIR__ . "/composer/vendor/autoload.php")) {
    require "composer/vendor/autoload.php";
}
// // Load user Composer autoloader
// if (file_exists(__DIR__."/../vendor/autoload.php")) {
//     require "../vendor/autoload.php";
// }

require_once "html.php";
require_once "functions.php";
require_once "classes/CSRF.php";
require_once "classes/Config.php";

class PermissionDeniedException extends Exception {
    
}

/**
 * A class for simple processing of web requests like http://webpy.org
 * 
 * 
 * Author 2007 Carsten Eckelmann
 */
class Web {

    public $_buffer;
    public $_logLevel;
    public $_logHandlers;
    public $_template;
    public $_templatePath;
    public $_templateExtension;
    public $_url;
    public $_context;
    public $_logMethod;
    public $_logParam;
    public $_logFolder = "log";
    public $_logFile = "web";
    public $_logExt = ".log";
    public $_logLevelArray;
    public $_action;
    public $_defaultHandler;
    public $_defaultAction;
    public $_layoutContentMarker;
    public $_notFoundTemplate;
    public $_layout;
    public $_headers;
    public $_module = null;
    public $_submodule = null;
    public $_modulePath;
    public $_moduleExtension;
    public $_modules;
    public $_hooks;
    public $_requestMethod;
    public $_action_executed = false;
    public $_action_redirected = false;
    public $_services;
    public $_moduleConfig;
    public $_paths;
    public $_loginpath = 'auth/login';
    public $_partialsdir = "partials";
    public $db;

    public $_scripts = array();
    public $_styles = array();
    
    /**
     * Constructor
     */
    function __construct() {
        $this->_logMethod = "file";
        $this->_logParam = "web.log";
        $this->_logFolder = "log";
        $this->_logFile = "web";
        $this->_logExt = ".log";
        $this->_logLevel = 0;
        $this->_buffer = null;
        $this->_context = array();
        $this->_templatePath = "templates";
        $this->_templateExtension = ".tpl.php";
        $this->_template = null;
        $this->_logLevelArray = array("debug", "info", "warn", "audit", "error");
        $this->_action = null;
        $this->_defaultHandler = "main";
        $this->_defaultAction = "index";
        $this->_layoutContentMarker = "body";
        $this->_notFoundTemplate = "404";
        $this->_paths = null;
        $this->_services = array();
        $this->_layout = "layout";
        $this->_headers = null;
        $this->_module = null;
        $this->_submodule = null;
        $this->_hooks = array();
        $this->_webroot = "/";
        $this->_actionMethod = null;
        spl_autoload_register(array($this, 'modelLoader'));
        $this->loadConfigurationFiles();
    }

    private function modelLoader($className) {
        $modules = $this->modules();
        foreach ($modules as $model) {
            $file = $this->getModuleDir($model) . 'models/' . ucfirst($className) . ".php";
            if (file_exists($file)) {
                include $file;
                //$this->logDebug("Class ".$file." loaded.");
                return true;
            }
        }
        $this->logDebug("Class " . $file . " NOT FOUND.");
        return false;
    }

    /**
     * Thanks to:
     * http://www.phpaddiction.com/tags/axial/url-routing-with-php-part-one/
     */
    private function _getCommandPath() {
        //$this->logDebug("REQUEST_URI: ".$_SERVER['REQUEST_URI']);
        $uri = explode('?', $_SERVER['REQUEST_URI']); // get rid of parameters
        $uri = $uri[0];
        // get rid of trailing slashes
        if (substr($uri, -1) == "/") {
            $uri = substr($uri, 0, -1);
        }
        $requestURI = explode('/', $uri);
        $scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
        for ($i = 0; $i < sizeof($scriptName); $i++) {
            // Checking is these vars are set makes the logout function not work
            // So we can just supress the warnings
            if (@$requestURI[$i] == @$scriptName[$i]) {
                unset($requestURI[$i]);
            }
        }
        return array_values($requestURI);
    }

    /**
     * Enqueue script adds the script entry to the Webs _script var which maintains
     * already registered scripts and helps prevent multiple additions of the same
     * library
     * 
     * @param Array $script
     */
    function enqueueScript($script) {
        if (!in_array($script, $this->_scripts)) {
            $this->_scripts[] = $script;
        }
    }
    
    /**
     * Enqueue style adds the style entry to the Webs _style var which maintains
     * already registered styles and helps prevent multiple additions of the same
     * library
     * 
     * @param Array $script
     */
    function enqueueStyle($style) {
        if (!in_array($style, $this->_styles)) {
            $this->_styles[] = $style;
        }
    }
    
    /**
     * Outputs the list of scripts to the buffer in order of weight descending
     */
    function outputScripts() {
        if (!empty($this->_scripts)) {
            usort($this->_scripts, array($this, "cmp_weights"));
            foreach($this->_scripts as $script) {
                echo "<script src='" . $script["uri"] . "'></script>";
            }
        }
    }
    
    /**
     * Outputs the list of styles to the buffer in order of weight descending
     */
    function outputStyles() {
        if (!empty($this->_styles)) {
            usort($this->_styles, array($this, "cmp_weights"));
            foreach($this->_styles as $style) {
                echo "<link rel='stylesheet' href='" . $style["uri"] . "'/>";
            }
        }
    }
    
    /**
     * Performs comparison for weights (for the enqueue functions above) to sort
     * by the "weight" key in descending order
     * 
     * @param Array $a
     * @param Array $b
     * @return int
     */
    public function cmp_weights($a, $b) {
        $aw = intval($a["weight"]);
        $bw = intval($b["weight"]);
        return ($aw === $bw ? 0 : ($aw < $bw ? 1 : -1));
    }
    
    /**
     * start processing of request
     * 1. look at the request parameter if the action parameter was set
     * 2. if not set, look at the pathinfo and use first
     */
    function start() {
        $this->initDB();

        // start the session
        // $sess = new SessionManager($this);
        session_name(SESSION_NAME);
        session_start();

        // Generate CSRF tokens and store them in the $_SESSION
        CSRF::getTokenID();
        CSRF::getTokenValue();

        $_SESSION['last_request'] = time();

        //$this->debug("Start processing: ".$_SERVER['REQUEST_URI']);        
        // find out which module to use
        $module_found = false;
        $action_found = false;

        $this->_paths = $this->_getCommandPath();

        // first find the module file
        if ($this->_paths && sizeof($this->_paths) > 0) {
            $this->_module = array_shift($this->_paths);
        }

        // then find the action
        if ($this->_paths && sizeof($this->_paths) > 0) {
            $this->_action = array_shift($this->_paths);
        }

        if (!$this->_module) {
            $this->_module = $this->_defaultHandler;
        }

        // see if the module is a sub module
        // eg. /sales-report/showreport/1..
        $hsplit = explode("-", $this->_module);
        $this->_module = array_shift($hsplit);
        $this->_submodule = array_shift($hsplit);

        if (!$this->_action) {
            $this->_action = $this->_defaultAction;
        }

        // try to load the action file
        if (!$this->_submodule) {
            $reqpath = $this->getModuleDir($this->_module) . '/actions/' . $this->_action . '.php';
            if (!file_exists($reqpath)) {
                $reqpath = $this->getModuleDir($this->_module) . '/' . $this->_module . ".actions.php";
            }
        } else {
            $reqpath = $this->getModuleDir($this->_module) . '/actions/' . $this->_submodule . '/' . $this->_action . '.php';
            if (!file_exists($reqpath)) {
                $reqpath = $this->getModuleDir($this->_module) . '/' . $this->_module . '.' . $this->_submodule . ".actions.php";
            }
        }

        // try to find action for the request type
        // using <module>_<action>_<type>()
        // or just <action>_<type>()

        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        $actionmethods[] = $this->_action . '_' . $this->_requestMethod;
        $actionmethods[] = $this->_action . '_ALL';

        // Check/validate CSRF token 
        $this->validateCSRF();

        //
        // if a module file for this url exists, then start processing
        //
        if (file_exists($reqpath)) {
            $this->ctx('webroot', $this->_webroot);
            $this->ctx('module', $this->_module);
            $this->ctx('submodule', $this->_module);
            $this->ctx('action', $this->_action);

            // CHECK ACCESS!!
            $this->checkAccess(); // will redirect if access denied!
            // load the module file
            require_once $reqpath;
        } else {
            $this->logError("No Action found for: " . $reqpath);
            $this->notFoundPage();
        }

        foreach ($actionmethods as $action_method) {
            if (function_exists($action_method)) {
                $action_found = true;
                $this->_actionMethod = $action_method;
                break;
            }
        }

        if ($action_found) {
            $this->ctx("loggedIn", $this->Auth->loggedIn());
            $this->ctx("error", $this->session('error'));
            $this->sessionUnset('error');
            $this->ctx("msg", $this->session('msg'));
            $this->sessionUnset('msg');
            $this->ctx("w", $this);

            try {
                // Load all listeners and call PRE ACTION listeners
                // phase this out! Hooks are better and faster
                $this->_callPreListeners();

                // call hooks, generic to specific
                $this->_callWebHooks("before");

                // Execute the action
                $method = $this->_actionMethod;
                $this->_action_executed = true;
                $method($this);

                // call hooks, generic to specific
                $this->_callWebHooks("after");

                // Call all POST ACTION listeners
                // INFO: These will also be called in the
                // redirect method!
                // phase this out!
                $this->_callPostListeners();
            } catch (PermissionDeniedException $ex) {
                $this->error($ex->getMessage());
            }

            // send headers first
            if ($this->_headers) {
                foreach ($this->_headers as $key => $val) {
                    header($key . ': ' . $val);
                }
            }
            $body = null;
            // evaluate template only when buffer is empty
            if (sizeof($this->_buffer) == 0) {
                $body = $this->fetchTemplate();
            } else {
                $body = $this->_buffer;
            }
            // but always check for layout
            // if ajax call don't do the layout
            if ($this->_layout && !$this->isAjax()) {
                $this->_buffer = null;
                $this->ctx($this->_layoutContentMarker, $body);
                $this->templateOut($this->_layout);
            } else {
                $this->_buffer = $body;
            }
            echo $this->_buffer;
        } else {
            $this->notFoundPage();
        }

        exit(); // nothing comes after start()!!!
    }

    /**
     * This creates and calls the following hooks:
     * 
     * core_web_before_get
     * core_web_before_get_[module]
     * core_web_before_get_[module]_[action]
     * core_web_before_get_[module]_[submodule]
     * core_web_before_get_[module]_[submodule]_[action]
     * core_web_after_get
     * core_web_after_get_[module]
     * core_web_after_get_[module]_[action]
     * core_web_after_get_[module]_[submodule]
     * core_web_after_get_[module]_[submodule]_[action]
     * core_web_before_post
     * core_web_before_post_[module]
     * core_web_before_post_[module]_[action]
     * core_web_before_post_[module]_[submodule]
     * core_web_before_post_[module]_[submodule]_[action]
     * core_web_after_post
     * core_web_after_post_[module]
     * core_web_after_post_[module]_[action]
     * core_web_after_post_[module]_[submodule]
     * core_web_after_post_[module]_[submodule]_[action]
     * 
     * @param unknown $type eg. before / after
     */
    private function _callWebHooks($type) {
        $request_method = strtolower($this->_requestMethod);
        
        // call hooks, generic to specific
        $this->callHook("core_web", $type . "_" . $request_method); // GET /*
        $this->callHook("core_web", $type . "_" . $request_method . "_" . $this->_module); // GET /module
        
        // Only call submodule hooks if a submodule is present, else call the module/action hook
        if (!empty($this->_submodule)) {
            $this->callHook("core_web", $type . "_" . $request_method . "_" . $this->_module . "_" . $this->_submodule); // GET /module-submodule/*
            $this->callHook("core_web", $type . "_" . $request_method . "_" . $this->_module . "_" . $this->_submodule . "_" . $this->_action); // GET /module-submodule/action
        } else {
            $this->callHook("core_web", $type . "_" . $request_method . "_" . $this->_module . "_" . $this->_action); // GET /module/action
        }
    }

    public function __get($name) {
        if ($name == ucfirst($name)) {
            return $this->service($name);
        }
    }

    private function initDB() {
        global $MYSQL_DB_HOST;
        global $MYSQL_USERNAME;
        global $MYSQL_PASSWORD;
        global $MYSQL_DB_NAME;
        global $MYSQL_DRIVER;

        $db_config = array(
            'hostname' => defaultVal(getenv('MYSQL_DB_HOST'), $MYSQL_DB_HOST),
            'username' => defaultVal(getenv('MYSQL_USERNAME'), $MYSQL_USERNAME),
            'password' => defaultVal(getenv('MYSQL_PASSWORD'), $MYSQL_PASSWORD),
            'database' => defaultVal(getenv('MYSQL_DB_NAME'), $MYSQL_DB_NAME),
            'driver' => defaultVal(getenv('MYSQL_DRIVER'), $MYSQL_DRIVER),
        );

        $this->db = new DbPDO($db_config); // Crystal::db($db_config);
    }

    function setModules($modules) {
        $this->_moduleConfig = $modules;
    }

    /**
     * Read Module configuration values
     * 
     * @param <type> $module
     * @param <type> $key
     * @return <type>
     */
    function moduleConf($module, $key) {
        return Config::get("{$module}.{$key}");
//        if (array_key_exists($module, $this->_moduleConfig) && array_key_exists($key, $this->_moduleConfig[$module])) {
//            return $this->_moduleConfig[$module][$key];
//        } else {
//            return null;
//        }
    }

    private function loadConfigurationFiles() {
        // Load System config first
        $baseDir = SYSTEM_PATH . '/modules';
        $this->scanModuleDirForConfigurationFiles($baseDir);

        // Load project module config second
        $baseDir = ROOT_PATH . '/modules';
        $this->scanModuleDirForConfigurationFiles($baseDir);
    }

    // Helper function for the above, scans a directory for config files in child folders
    private function scanModuleDirForConfigurationFiles($dir = "") {
        global $modules;

        // Check that dir is dir
        if (is_dir($dir)) {

            // Scan directory
            $dirListing = scandir($dir);
            if (!empty($dirListing)) {

                // Loop through listing
                foreach ($dirListing as $item) {
                    $searchingDir = $dir . "/" . $item;
                    if (is_dir($searchingDir) and $item[0] !== '.') {

                        // If is also a directory, look for config.php file
                        if (file_exists($searchingDir . "/config.php")) {
                            include($searchingDir . "/config.php");
                        }
                    }
                }
            }
        }
    }

    private function validateCSRF() {
        // Check for CSRF token and that we have a valid request method
        if (!CSRF::isValid($this->_requestMethod)) {
            @$this->logError("CSRF Detected from " . $this->requestIpAddress());
            header("HTTP/1.0 403 Forbidden");
            echo "Cross site request forgery detected. Your IP has been logged";
            die();
        }
    }

    function isAjax() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    /**
     * Check if the currently logged in user
     * has access to this path
     *
     * @param <type> $msg
     * @return <type>
     */
    function checkAccess($msg = "Access Restricted") {
        $submodule = $this->_submodule ? "-" . $this->_submodule : "";
        $path = $this->_module . $submodule . "/" . $this->_action;
        if ($this->Auth && $this->Auth->user()) {
            $user = $this->Auth->user();
            $usrmsg = $user ? " for " . $user->login : "";
            if (!$this->Auth->allowed($path)) {
                $this->logInfo("Access Denied to " . $path . $usrmsg . " from " . $this->requestIpAddress());
                // redirect to the last allowed page 
                if ($this->Auth->allowed($_SESSION['LAST_ALLOWED_URI'])) {
                    $this->error($msg, $_SESSION['LAST_ALLOWED_URI']);
                } else {
                    // Logout user
                    $this->sessionDestroy();
                    $this->error($msg, "/auth/login");
                }
            }
        } else if ($this->Auth && !$this->Auth->loggedIn() && $path != $this->_loginpath && !$this->Auth->allowed($path)) {
            $_SESSION['orig_path'] = $_SERVER['REQUEST_URI'];
            $this->redirect($this->localUrl($this->_loginpath));
        }
        // Saving the last allowed uri so we can
        // redirect to it from a failed call
        if (!$this->isAjax()) {
            $_SESSION['LAST_ALLOWED_URI'] = $_SERVER['REQUEST_URI'];
        }
        return true;
    }

    /**
     * 
     * Return the mimetype for a file path
     * @param $filename (including path)
     * @return string
     */
    function getMimetype($filename) {
        $mime = "text/html";
        // finfo_open was introduced in 5.3, I think we can safely assume that 5.3 would be a minimum requirement
        // these days, plus we should avoid using system
        // if (function_exists("finfo_open")) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $mime = finfo_file($finfo, $filename);
        finfo_close($finfo);
        // }
        //  else {
        //     ob_start();
        //     system("file -i -b {$filepath}");
        //     $output = ob_get_clean();
        //     $output = explode("; ",$output);
        //     if ( is_array($output) ) {
        //         $output = $output[0];
        //     }
        //     $mime = $output;
        // }
        return $mime;
    }

    /**
     * Send the contents of the file to the client browser
     * as raw data.
     * 
     * @param string $filename
     */
    function sendFile($filename) {
        if (file_exists($filename)) {
            $filesystem = $this->File->getFilesystem(dirname($filename));
            $file = $this->File->getFileObject($filesystem, $filename);
            header("Content-Type: " . $this->getMimetype($filename));
            echo $file->getContent();
        } else {
            header("HTTP/1.1 404 Not Found");
        }
        exit;
    }

    /**
     * Convenience Method for creating menu's
     * This will check if $path is allowed
     * and will then return an html link or nothing
     *
     * if $array is set will also add the link to the array
     *
     * @param string $path
     * @param string $title
     * @param array $array
     * @return string
     */
    function menuLink($path, $title, &$array = null) {
        $class = "";
        if (startsWith($path, $this->currentModule())) {
            $class = "current";
        }
        $link = $this->Auth->allowed($path, Html::a($this->localUrl($path), $title, $title, $class));
        if ($array !== null) {
            $array[] = $link;
        }
        return $link;
    }

    /**
     * Same as menuLink but displays a button instead
     * @param string $path
     * @param string $title
     * @param string $array
     * @return string html code
     */
    function menuButton($path, $title, &$array = null) {
        $link = $this->Auth->allowed($path, Html::b($this->localUrl($path), $title));
        if ($array !== null) {
            $array[] = $link;
        }
        return $link;
    }

    /**
     * Convenience Method for creating menu's
     * This will check if $path is allowed
     * and will then return an html link or nothing
     *
     * This will create a link which will open a popup box
     *
     * if $array is set will also add the link to the array
     *
     * @param string $path
     * @param string $title
     * @param array $array
     */
    function menuBox($path, $title, &$array = null) {
        $link = $this->Auth->allowed($path, Html::box($this->localUrl($path), $title));
        if ($array !== null) {
            $array[] = $link;
        }
        return $link;
    }

    /**
     * Creates a url prefixed with the webroot
     *
     * @param string $link
     * @return string html code
     */
    function localUrl($link = null) {
        if (strpos($link, "/") !== 0) {
            $link = "/" . $link;
        }
        return $this->webroot() . $link;
    }

    /**
     * Redirect to $url and display an
     * error message
     *
     * @param <type> $msg
     * @param <type> $url
     */
    function error($msg, $url = "") {
        $_SESSION['error'] = $msg;
        $this->ctx('error', $msg);
        $this->redirect($this->localUrl($url));
    }

    // This function generates an error message based on whats returned from the DbObject validation method
    // $w is for the error() function
    // $object is the object that one is saving/updating whatever
    // $type is for the message returned, i.e. "Updating this $type failed"
    // $response is the reponse array from the validation method
    // $isUpdating is a helper for the message i.e. creating/updating
    // $returnUrl is where the redirection in error() will go
    function errorMessage($object, $type = null, $response = true, $isUpdating = false, $returnUrl = "/") {
        if ($response === true || empty($type)) {
            return;
        } else {
            if (is_array($response)) {
                $errorMsg = ($isUpdating ? "Updating" : "Creating") . " this $type failed because<br/><br/>\n";

                foreach ($response["invalid"] as $property => $reason) {
                    foreach ($reason as $r) {
                        $errorMsg .= $object->getHumanReadableAttributeName($property) . ": $r <br/>\n";
                    }
                }
                $this->error($errorMsg, $returnUrl);
            } else {
                $this->error(($isUpdating ? "Updating" : "Creating") . " this $type failed.", $returnUrl);
            }
        }
    }

    /**
     * Redirect to $url and display
     * a message
     *
     * @param <type> $msg
     * @param <type> $url
     */
    function msg($msg, $url = "") {
        $_SESSION['msg'] = $msg;
        $this->ctx('msg', $msg);
        $this->redirect($this->localUrl($url));
    }

    /**
     * Sends 404 header and displays not found message<br/>
     * <b>THIS EXITS the current process</b>
     */
    function notFoundPage() {
        $this->logWarn("Action not found: " . $this->_module . "/" . $this->_action);
        if ($this->templateExists($this->_notFoundTemplate)) {
            header("HTTP/1.0 404 Not Found");
            echo $this->fetchTemplate($this->_notFoundTemplate);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo '<p align="center">Sorry, page not found.</p>';
        }
        exit();
    }

    function internalLink($title, $module, $action = null, $params = null) {
        if (!$this->Auth->allowed($module, $action)) {
            return null;
        } else {
            return "<a href='" . $this->localUrl("/" . $module . "/" . $action . $params) . "'>" . $title . "</a>";
        }
    }

    /**
     * Return all modules currently in the codebase
     */
    function modules() {
        return Config::keys();
    }

    /**
     * 
     * Returns the file path for a module if it exists,
     * otherwise returns null
     * @param string $module
     * @return Ambigous <NULL, string>
     */
    function getModuleDir($module) {
        // check for explicit module path first
        $basepath = $this->moduleConf($module, 'path');
        if (!empty($basepath)) {
            $path = $basepath . '/' . $module . '/';
            return file_exists($path) ? $path : null;
        }

        return null;
    }

    function moduleUrl($module) {
        return $this->webroot() . '/' . $this->getModuleDir($module);
    }

    /**
     * Return a preloaded Service as
     * defined in a model.php inside
     * as module.
     *
     * @param <type> $name
     * @return <type>
     */
    function & service($name) {
        $name = ucfirst($name);
        if (!key_exists($name, $this->_services)) {
            $cname = $name . "Service";
            if (class_exists($cname)) {
                $s = new $cname($this);
                // initialise
                if (method_exists($s, "__init")) {
                    $s->__init();
                }
                $this->_services[$name] = & $s;
            }
        }
        return $this->_services[$name];
    }

    /**
     * Call and return code for a partial template.
     * 
     * This works like an action/template except that it can't be called directly from a url.
     * 
     * Partials don't have access to the global context and do not store anything in the global context!
     * 
     * @param string $name
     * @param array $params
     * @param string $module
     * @param string $method
     */
    function partial($name, $params = null, $module = null, $method = "ALL") {
        if ($module === null) {
            $module = $this->_module;
        }
        // save current output buffer
        $oldbuf = $this->_buffer;
        $this->_buffer = null;

        // save the current context
        $oldctx = $this->_context;
        $this->_context = array();

        // try to find the partial action and execute
        $partial_action_file = implode("/", array($this->getModuleDir($module), $this->_partialsdir, "actions", $name . ".php"));
        if (file_exists($partial_action_file)) {
            require_once($partial_action_file);

            // now execute the action
            $partial_action = $name . "_" . $method;
            if (function_exists($partial_action)) {
                $partial_action($this, $params);
            }
        }

        $currentbuf = $this->_buffer;

        if (empty($currentbuf)) {
            // try to find the partial template and execute if found
            $partial_template_file = implode("/", array($this->getModuleDir($module), $this->_partialsdir, "templates", $name . $this->_templateExtension));
            if (file_exists($partial_template_file)) {
                $tpl = new WebTemplate();
                $this->ctx("w", $this);
                $tpl->set_vars($this->_context);
                $currentbuf = $tpl->fetch($partial_template_file);
            }
        }

        // restore output buffer and context
        $this->_buffer = $oldbuf;
        $this->_context = $oldctx;

        return $currentbuf;
    }

    /**
     * Call hook method to invoke other modules helper functions
     * 
     * @param String module
     * @param String $function
     * @param Mixed $data
     */
    public function callHook($module = null, $function = null, $data = null) {
        if (empty($module) or empty($function)) {
            return;
        }

        // Build _hook registry if empty
        if (empty($this->_hooks)) {
            foreach ($this->_moduleConfig as $modulename => $conf) {
                if (array_key_exists("hooks", $conf)) {
                    foreach ($conf["hooks"] as $hook) {
                        $this->_hooks[$hook][] = $modulename;
                    }
                }
            }
        }
        
        // Check that the module calling has subscribed to hooks
        if (!array_key_exists($module, $this->_hooks)) {
            return;
        }

        // Loop through each registered module to try and invoke the function
        foreach ($this->_hooks[$module] as $toInvoke) {
            // Check that the hook impl module that we are invoking is a module
            if (!in_array($toInvoke, $this->modules())) {
                continue;
            }

            // Check if the file exits
            if (!file_exists($this->getModuleDir($toInvoke) . "$toInvoke.hooks.php")) {
                continue;
            }

            // Include and check if function exists
            include_once ($this->getModuleDir($toInvoke) . "$toInvoke.hooks.php");

            $hook_function_name = ($toInvoke . "_" . $module . "_" . $function);
            if (!function_exists($hook_function_name)) {
                continue;
            }

            // Call function
            $hook_function_name($this, $data);
        }
    }

    /////////////////////////////////// Template stuff /////////////////////////

    function setLayout($l) {
        $this->_layout = $l;
    }

    function getLayout($l) {
        $this->_layout = $l;
    }

    function setTemplate($t) {
        $this->_template = $t;
    }

    function getTemplate() {
        return $this->_template;
    }

    /**
     * set the path where Web looks for template files
     */
    function setTemplatePath($path) {
        $this->_templatePath = $path;
    }

    function setTemplateExtension($ext) {
        $this->_templateExtension = $ext;
    }

    /**
     * check if a template file exists!
     */
    function templateExists($tmpl) {
        return file_exists($this->getTemplateRealFilename($tmpl));
    }

    function getTemplateRealFilename($tmpl) {
        return $tmpl . $this->_templateExtension;
    }

    /**
     * Evaluates a template in the web context and
     * returns it as string. The template is searched for
     * in the following order: <br/>
     * <pre>
     * /<moduledir>/<module>/templates/<submodule>/<action>_<httpmethod>.tpl.php
     * /<moduledir>/<module>/templates/<submodule>/<action>.tpl.php
     * /<moduledir>/<module>/templates/<submodule>/<submodule>.tpl.php
     * /<moduledir>/<module>/templates/<action>_<httpmethod>.tpl.php
     * /<moduledir>/<module>/templates/<action>.tpl.php
     * /<moduledir>/<module>/templates/<module>.tpl.php
     * /<moduledir>/<module>/<action>_<httpmethod>.tpl.php
     * /<moduledir>/<module>/<action>.tpl.php
     * /<moduledir>/<module>/<module>.tpl.php
     * /<templatedir>/<action>_<httpmethod>.tpl.php
     * /<templatedir>/<action>.tpl.php
     * /<templatedir>/<module>.tpl.php
     * </pre>
     */
    function fetchTemplate($name = null) {
        if ($this->_submodule) {
            $paths[] = implode("/", array($this->getModuleDir($this->_module), $this->_templatePath, $this->_submodule));
        }
        $paths[] = implode("/", array($this->getModuleDir($this->_module), $this->_templatePath));
        $paths[] = implode("/", array($this->getModuleDir($this->_module)));
        $paths[] = implode("/", array($this->_templatePath, $this->_module));
        $paths[] = $this->_templatePath;
        
        // Add system fallback
        $paths[] = SYSTEM_PATH . "/" . $this->_templatePath;

        $names = array();
        if ($name) {
            $names[] = $name;
        } else {
            $names[] = $this->_actionMethod;
            $names[] = $this->_action;
            if ($this->_submodule) {
                $names[] = $this->_submodule;
            } else {
                $names[] = $this->_module;
            }
        }

        // we need to find a template from a combination of paths and names
        // in the above arrays from the most specific to the most broad
        $template = null;
        foreach ($paths as $path) {
            foreach ($names as $nam) {
                if ($nam && $this->templateExists($path . '/' . $nam)) {
                    $template = $path . '/' . $nam;
                    break 2; // break out of both loops
                } else {
                    //$this->logDebug("no template @ ".$path.'/'.$nam);
                }
            }
        }

        if (!$template) {
            $this->logError("No Template found.");
            return null;
        }
        $tpl = new WebTemplate();
        $tpl->set_vars($this->_context);
        return $tpl->fetch($this->getTemplateRealFilename($template));
    }

    /**
     * evaluate template and put the string into
     * the web context for inclusion in other
     * templates
     */
    function putTemplate($key, $template) {
        $this->ctx($key, $this->fetchTemplate($template));
    }

    /**
     * This will execute the passed in template
     * instead of the default one. The layout will
     * still be used!
     */
    function templateOut($template) {
        $this->out($this->fetchTemplate($template));
    }

    /**
     * prints to the page
     * if this is used, then the template will NOT be called
     * automatically! But the layout will still be used.
     */
    function out($txt) {
        $this->_buffer .= $txt;
    }

    function webroot() {
        return $this->_webroot;
    }

    /**
     * Turns a variable list of string arguments into
     * context entries loaded with the values of the url segments.
     *
     * eg: Given a URL with /one/two/three, calling
     *     pathMatch("eins","zwei","drei") will insert into the context
     *     ("eins" => "one", "zwei" => "two", "drei" => "three")
     *
     * @param multiple string params, which will be turned into ctx entries
     * @return an array of key, value pairs
     */
    function pathMatch() {
        $match = array();
        for ($i = 0; $i < func_num_args(); $i++) {
            $param = func_get_arg($i);

            $val = !empty($this->_paths[$i]) ? urldecode($this->_paths[$i]) : null;

            if (is_array($param)) {
                $key = $param[0];
                if (is_null($val) && isset($param[1])) {
                    $val = $param[1]; // use default parameter
                }
            } else {
                $key = $param;
            }
            $this->ctx($key, $val);
            $match[$key] = $val;
        }
        return $match;
    }

    /**
     * Returns the request value in a safe way
     * without generating warning.
     *
     * @param <type> $key
     * @param <type> $default
     * @return <type>
     */
    function request($key, $default = null) {
        if (array_key_exists($key, $_REQUEST) && is_array($_REQUEST[$key])) {
            foreach ($_REQUEST[$key] as &$k) {
                urldecode($k);
            }
            return $_REQUEST[$key];
        }
        return array_key_exists($key, $_REQUEST) ? urldecode($_REQUEST[$key]) : $default;
    }

    function requestIpAddress() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Return the current module
     * @return <type>
     */
    function currentModule() {
        return $this->_module;
    }

    /**
     * Return the current module
     * @return <type>
     */
    function currentSubModule() {
        return $this->_submodule;
    }

    /**
     * Return the current Action
     */
    function currentAction() {
        return $this->_action;
    }

    /**
     * Call all PRE ACTION listeners
     */
    function _callPreListeners() {
        foreach ($this->modules() as $h) {
            $lfile = $this->getModuleDir($h) . $h . ".listeners.php";
            if (file_exists($lfile)) {
                require_once $lfile;
                $action = $h . "_listener_PRE_ACTION";
                if (function_exists($action)) {
                    $action($this);
                }
            }
        }
    }

    /**
     * Call all POST ACTION listeners
     * (rely on listener files included from pre_listener call!
     */
    function _callPostListeners() {
        foreach ($this->modules() as $h) {
            $action = $h . "_listener_POST_ACTION";
            if (function_exists($action)) {
                $action($this);
            }
        }
    }

    /**
     * validates the request parameters according to
     * the rules passed in $valarray. It must be of the
     * following form:
     *
     * array(
     *   array("<param-name>","<regexp>","<error message>"),
     *   array("<param-name>","<regexp>","<error message>"),
     *   ...
     * )
     *
     * returns an array which contains all produced error
     * messages
     */
    function validate($valarray) {
        if (!$valarray || !sizeof($valarray))
            return null;
        $error = array();
        foreach ($valarray as $rule) {
            $param = $rule[0];
            $regex = $rule[1];
            $message = $rule[2];
            $val = $_REQUEST[$param];
            if (!preg_match("/" . $regex . "/", $val)) {
                $error[] = $message;
            }
        }
        return $error;
    }

    /**
     * Return current request method
     * @return <type>
     */
    function currentRequestMethod() {
        return $this->_requestMethod;
    }

    /**
     * log functions
     */
    function logDebug($msg) {
        $this->_log(0, $msg);
    }

    function isDebug() {
        return $this->_logLevel == 0;
    }

    function logInfo($msg) {
        $this->_log(1, $msg);
    }

    function isInfo() {
        return $this->_logLevel <= 1;
    }

    function logWarn($msg) {
        $this->_log(2, $msg);
    }

    function isWarn() {
        return $this->_logLevel <= 2;
    }

    function logAudit($msg) {
        $this->_log(3, $msg);
    }

    function isAudit() {
        return $this->_logLevel <= 3;
    }

    function logError($msg) {
        $this->_log(4, $msg);
    }

    function isError() {
        return $this->_logLevel <= 4;
    }

    function getPath() {
        return implode("/", $this->_paths);
    }

    /**
     * Get or Set a value in the current context.
     * 
     * If $append is true, append the value to the existing value.
     * 
     * If $value is null, the current value will be returned.
     * 
     * @param string $key
     * @param string $value
     * @param boolean $append
     */
    function ctx($key, $value = null, $append = false) {
        if ($value == null) {
            return !empty($this->_context[$key]) ? $this->_context[$key] : null;
        } else {
            if ($append) {
                $this->_context[$key] .= $value;
            } else {
                $this->_context[$key] = $value;
            }
        }
    }

    /**
     * get/put a session value
     */
    function session($key, $value = null) {
        if ($value == null) {
            return !empty($_SESSION[$key]) ? $_SESSION[$key] : null;
        } else {
            $_SESSION[$key] = $value;
        }
    }

    function sessionUnset($key) {
        unset($_SESSION[$key]);
    }

    function sessionDestroy() {
        $_SESSION = array();

        session_name(SESSION_NAME);

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }

    /**
     * Send a browser redirect
     */
    function redirect($url) {
        // stop endless loops!!
        if ($this->_action_redirected) {
            return;
        }
        $this->_action_redirected = true;
        //$this->logDebug("Redirect: ".$url);
        // although we are redirecting we should
        // still call the POST modules and listeners
        // but only if we got redirected from a real action
        // we don't want to call these if redirected from
        // a role check or pre module/listener
        if ($this->_action_executed) {
            $this->_callWebHooks("after");
            $this->_callPostListeners();
        }

        header("Location: " . trim($url));
        exit();
    }

    /**
     * set http header values
     */
    function sendHeader($key, $value) {
        //$this->logDebug("Header[".$key."] = '".$value."'");
        $this->_headers[$key] = $value;
    }

    /**
     * returns a string representation of everything
     * session. request, url, headers, modules,
     * template contexts. This can then be displayed on the page
     * or written to the log.
     */
    function dump() {
        echo "<pre>";
        echo "<b>========= WEB =========</b>";
        print_r($this);
        echo "<b>========= REQUEST =========</b>";
        print_r($_REQUEST);
        echo "<b>========= SESSION =========</b>";
        print_r($_SESSION);
        echo "</pre>";
    }

    function setLogLevel($levelstring) {
        $this->_logLevel = array_search($levelstring, $this->_logLevelArray);
    }

    function _log($level, $msg) {
        if ($level < $this->_logLevel)
            return;
        if ($this->_logMethod == "file") {
            $this->_logToFile($level, $msg);
        }
    }

    function _logToFile($level, $msg) {
        //$f=fopen($this->_logParam, 'a');
        $f = fopen($this->_logFolder . "/" . $this->_logFile . "-" . date("Y-m-d") . $this->_logExt, 'a');
        fwrite($f, date('d/m/Y H:i:s') . ' ' . strtoupper($this->_logLevelArray[$level]) . ' ' . $msg . "\n");
        fclose($f);
    }

    /**
     * 
     * Shortcut for setting the title of a page
     * 
     * @param String $title
     */
    function setTitle($title) {
        $this->ctx("title", $title);
    }

}

///////////////////////////////////////////////////////////////////////////////
//                                                                           //
//                           Page Template System                            //
//                                                                           //
///////////////////////////////////////////////////////////////////////////////


class WebTemplate {

    public $vars; /// Holds all the template variables

    /**
     * Constructor
     *
     * @param string $path the path to the templates
     *
     * @return void
     */

    function WebTemplate() {
        $this->vars = array();
    }

    /**
     * Set a template variable.
     *
     * @param string $name name of the variable to set
     * @param mixed $value the value of the variable
     *
     * @return void
     */
    function set($name, $value) {
        $this->vars[$name] = $value;
    }

    /**
     * Set a bunch of variables at once using an associative array.
     *
     * @param array $vars array of vars to set
     * @param bool $clear whether to completely overwrite the existing vars
     *
     * @return void
     */
    function set_vars($vars, $clear = false) {
        if ($clear) {
            $this->vars = $vars;
        } else {
            if (is_array($vars))
                $this->vars = array_merge($this->vars, $vars);
        }
    }

    /**
     * Open, parse, and return the template file.
     *
     * @param string string the template file name
     *
     * @return string
     */
    function fetch($file) {
        extract($this->vars);          // Extract the vars to local namespace
        ob_start();                    // Start output buffering
        include($file);  // Include the file
        $contents = ob_get_contents(); // Get the contents of the buffer
        ob_end_clean();                // End buffering and discard
        return $contents;              // Return the contents
    }

}

/**
 * An extension to Template that provides automatic caching of
 * template contents.
 */
class CachedTemplate extends WebTemplate {

    public $cache_id;
    public $expire;
    public $cached;

    /**
     * Constructor.
     *
     * @param string $path path to template files
     * @param string $cache_id unique cache identifier
     * @param int $expire number of seconds the cache will live
     *
     * @return void
     */
    function CachedTemplate($path, $cache_id = null, $expire = 900) {
        $this->WebTemplate($path);
        $this->cache_id = $cache_id ? 'cache/' . md5($cache_id) : $cache_id;
        $this->expire = $expire;
    }

    /**
     * Test to see whether the currently loaded cache_id has a valid
     * corrosponding cache file.
     *
     * @return bool
     */
    function is_cached() {
        if ($this->cached)
            return true;

        // Passed a cache_id?
        if (!$this->cache_id)
            return false;

        // Cache file exists?
        if (!file_exists($this->cache_id))
            return false;

        // Can get the time of the file?
        if (!($mtime = filemtime($this->cache_id)))
            return false;

        // Cache expired?
        if (($mtime + $this->expire) < time()) {
            @unlink($this->cache_id);
            return false;
        } else {
            /**
             * Cache the results of this is_cached() call.  Why?  So
             * we don't have to double the overhead for each template.
             * If we didn't cache, it would be hitting the file system
             * twice as much (file_exists() & filemtime() [twice each]).
             */
            $this->cached = true;
            return true;
        }
    }

    /**
     * This function returns a cached copy of a template (if it exists),
     * otherwise, it parses it as normal and caches the content.
     *
     * @param $file string the template file
     *
     * @return string
     */
    function fetch_cache($file) {
        if ($this->is_cached()) {
            $fp = @fopen($this->cache_id, 'r');
            $contents = fread($fp, filesize($this->cache_id));
            fclose($fp);
            return $contents;
        } else {
            $contents = $this->fetch($file);

            // Write the cache
            if ($fp = @fopen($this->cache_id, 'w')) {
                fwrite($fp, $contents);
                fclose($fp);
            } else {
                die('Unable to write cache.');
            }

            return $contents;
        }
    }

}

/**
 * License for Template and CachedTemplate classes:
 *
 * Copyright (c) 2003 Brian E. Lozier (brian@massassi.net)
 *
 * set_vars() method contributed by Ricardo Garcia (Thanks!)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

