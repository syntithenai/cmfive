<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo ucfirst($w->currentModule()); ?><?php echo!empty($title) ? ' - ' . $title : ''; ?></title>
        <link rel="icon" href="<?php echo WEBROOT; ?>/templates/img/favicon.png" type="image/png"/>

        <?php
        $w->enqueueStyle(array("name" => "normalize.css", "uri" => "/system/templates/js/foundation-5.0.2/css/normalize.css", "weight" => 1010));
        $w->enqueueStyle(array("name" => "foundation.css", "uri" => "/system/templates/js/foundation-5.0.2/css/foundation.css", "weight" => 1005));
        $w->enqueueStyle(array("name" => "style.css", "uri" => "/system/templates/css/style.css", "weight" => 1000));
        $w->enqueueStyle(array("name" => "tablesorter.css", "uri" => "/system/templates/css/tablesorter.css", "weight" => 990));
        $w->enqueueStyle(array("name" => "datePicker.css", "uri" => "/system/templates/css/datePicker.css", "weight" => 980));
        $w->enqueueStyle(array("name" => "jquery-ui-1.8.13.custom.css", "uri" => "/system/templates/js/jquery-ui-new/css/custom-theme/jquery-ui-1.8.13.custom.css", "weight" => 970));
        $w->enqueueStyle(array("name" => "liveValidation.css", "uri" => "/system/templates/css/liveValidation.css", "weight" => 960));
        $w->enqueueStyle(array("name" => "colorbox.css", "uri" => "/system/templates/js/colorbox/colorbox/colorbox.css", "weight" => 950));
        $w->enqueueStyle(array("name" => "jquery.asmselect.css", "uri" => "/system/templates/css/jquery.asmselect.css", "weight" => 940));

        $w->enqueueScript(array("name" => "modernizr.js", "uri" => "/system/templates/js/foundation-5.0.2/js/modernizr.js", "weight" => 1010));
        $w->enqueueScript(array("name" => "jquery.js", "uri" => "/system/templates/js/foundation-5.0.2/js/jquery.js", "weight" => 1000));
        $w->enqueueScript(array("name" => "jquery.tablesorter.js", "uri" => "/system/templates/js/tablesorter/jquery.tablesorter.js", "weight" => 990));
        $w->enqueueScript(array("name" => "jquery.tablesorter.pager.js", "uri" => "/system/templates/js/tablesorter/jquery.tablesorter.pager.js", "weight" => 980));
        $w->enqueueScript(array("name" => "jquery.colorbox-min.js", "uri" => "/system/templates/js/colorbox/colorbox/jquery.colorbox-min.js", "weight" => 970));
        $w->enqueueScript(array("name" => "jquery-ui-1.8.13.custom.min.js", "uri" => "/system/templates/js/jquery-ui-new/js/jquery-ui-1.8.13.custom.min.js", "weight" => 960));
        $w->enqueueScript(array("name" => "jquery-ui-timepicker-addon.js", "uri" => "/system/templates/js/jquery-ui-timepicker-addon.js", "weight" => 950));
        $w->enqueueScript(array("name" => "livevalidation.js", "uri" => "system/templates/js/livevalidation.js", "weight" => 940));
        $w->enqueueScript(array("name" => "main.js", "uri" => "/system/templates/js/main.js", "weight" => 995));
        $w->enqueueScript(array("name" => "jquery.asmselect.js", "uri" => "/system/templates/js/jquery.asmselect.js", "weight" => 920));
        $w->enqueueScript(array("name" => "boxover.js", "uri" => "/system/templates/js/boxover.js", "weight" => 910));
        $w->enqueueScript(array("name" => "ckeditor.js", "uri" => "/system/templates/js/ckeditor/ckeditor.js", "weight" => 900));
        $w->enqueueScript(array("name" => "Chart.js", "uri" => "/system/templates/js/chart-js/Chart.js", "weight" => 890));

        $w->outputStyles();
        $w->outputScripts();
        ?>
        <script type="text/javascript">

            var current_tab = 0;

            function switchTab(num) {
                if (num == current_tab)
                    return;
                $('#tab-' + current_tab).hide();
                $('#tab-link-' + current_tab).removeClass("active");
                $('#tab-' + num).show().addClass("active");
                $('#tab-link-' + num).addClass("active");
                current_tab = num;
            }

            $(document).ready(function() {
                $(".msg").delay(3000).fadeOut(3000);
                $(".error").delay(6000).fadeOut(3000);
                $("table.tablesorter").tablesorter({dateFormat: "uk", widthFixed: true, widgets: ['zebra']});
<?php
$tab = $w->request('tab');
if (!empty($tab)) :
    ?>
                    switchTab("<?php echo $tab; ?>");
<?php else: ?>

                    $(".tab-head").children("a").each(function() {
                        $(this).bind("click", {alink: this}, function(event) {
                            changeTab(event.data.alink.hash);
                        });
                    });

                    // Change tab if hash exists
                    var hash = window.location.hash.split("#")[1];
                    if (hash && hash.length > 0) {
                        changeTab(hash);
                    } else {
                        $(".tab-head > a:first").trigger("click");
                    }
<?php endif; ?>
            });

            // Try and prevent multiple form submissions
            $("input[type=submit]").click(function() {
                $(this).hide();
            });
            $(document).bind('cbox_complete', function() {
                $("input[type=submit]").click(function() {
                    $(this).hide();
                });
            });

        </script>
    </head>
    <body>
        <div class="row-fluid">
            <nav class="top-bar" data-topbar data-options="is_hover: false">
                <ul class="title-area">
                    <li class="name">
                        <!--<h1><a href="/"><?php // echo str_replace("http://", "", $w->moduleConf('main', 'company_url'));   ?></a></h1>-->
                    </li>
                    <li class="toggle-topbar menu-icon"><a href="#">Menu</a></li>
                </ul>

                <section class="top-bar-section">
                    <!-- Right Nav Section -->
                    <ul class="right">
                        <!-- Search bar -->
                        <li class="has-form">
                            <form action="<?php echo WEBROOT; ?>/search/results" method="GET">
                                <input type="hidden" name="<?php echo CSRF::getTokenID(); ?>" value="<?php echo CSRF::getTokenValue(); ?>" />
                                <div class="row collapse">
                                    <div class="large-8 small-8 columns">
                                        <input style="height: 1.8rem;" type="text" id="q" name="q" value="<?php echo!empty($_REQUEST['q']) ? $_REQUEST['q'] : ''; ?>" placeholder="Search..." />
                                    </div>
                                    <!--<div class="large-4 small-4 columns">-->
                                    <?php //echo Html::select("idx", $w->service('Search')->getIndexes(), (!empty($_REQUEST['idx']) ? $_REQUEST['idx'] : null), null, null, "Search All"); ?>
                                    <input type="hidden" name="p" value="1"/>
                                    <input type="hidden" name="ps" value="25"/>
                                    <!--</div>-->
                                    <div class="large-4 small-4 columns">
                                        <button class="alert button expand">Search</button>
                                    </div>
                                </div>
                            </form>
                        </li>
                        <!-- End search bar -->
                        <!-- User Profile dropdown -->
                        <?php if ($w->Auth->user()): ?>
                            <li class="has-dropdown">
                                <a href="#"><?php echo $w->Auth->user()->getShortName(); ?></a>
                                <?php
                                echo Html::ul(
                                        array(
                                    $w->menuBox("auth/profile/box", "Profile"),
                                    $w->menuLink("auth/logout", "Logout")
                                        ), null, "dropdown");
                                ?>    
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Left Nav Section -->
                    <ul class="left">
                        <?php if ($w->Auth->loggedIn()) : ?>
                            <li><?php echo $w->menuLink($w->Main->getUserRedirectURL(), "Home"); ?></li>
                            <?php foreach ($w->_moduleConfig as $name => $options) {
                                // Check if config is set to display on topmenu
                                if ($options['topmenu']) {
                                    // Check for navigation
                                    if (method_exists($name . "Service", "navigation")) : ?>
                                        <li class="has-dropdown <?php $w->_module == $name ? 'active' : ''; ?>">
                                            <a href="#"><?php echo ucfirst($name); ?></a>
                                            <?php echo Html::ul($w->service($name)->navigation($w), null, "dropdown"); ?>
                                        </li>
                                    <?php else: ?>
                                        <li><?php echo $w->menuLink($name . "/index", ucfirst($name)); ?></li>
                                    <?php endif;
                                }
                            }
                        
                            if ($w->Auth->allowed('help/view')) : ?>
                                <li><?php echo Html::box(WEBROOT . "/help/view/" . $w->_module . ($w->_submodule ? "-" . $w->_submodule : "") . "/" . $w->_action, "HELP", false, true, 750, 500); ?> </li>
                            <?php endif;
                        endif; ?>
                    </ul>
                </section>
            </nav>
        </div>

        <table width="100%" align="center" cellpadding="0" cellspacing="0">
            <?php
            if (!empty($boxes)) {
                foreach ($boxes as $btitle => $box) {
                    ?>
                    <div class="box">
                        <div class="boxtitle flt"><?php echo ucfirst($btitle); ?></div>
                        <div class="menubg flt">
        <?php echo $box; ?>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>

        </td>

        <td valign="top" height="100%">
            <div id="center">
                <div id="body">
                    <div class="content-header"><?php echo!empty($title) ? $title : ucfirst($w->currentModule()); ?></div>
                    <?php if (!empty($error) || !empty($msg)) : ?>
                            <?php $type = !empty($error) ? array("name" => "error", "class" => "warning") : array("name" => "msg", "class" => "info"); ?>
                        <div data-alert class="alert-box <?php echo $type["class"]; ?>">
    <?php echo $$type["name"]; ?>
                            <a href="#" class="close">&times;</a>
                        </div>
<?php endif; ?>

                    <div class="row">
<?php echo!empty($body) ? $body : ''; ?>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="2"><div id="footer">Copyright <?php echo date('Y'); ?> <a href="<?php echo $w->moduleConf('main', 'company_url'); ?>"><?php echo $w->moduleConf('main', 'company_name'); ?></a></div></td>
    </tr>
</table>

<!-- Test foudnation include -->

<script type="text/javascript" src="<?php echo $webroot; ?>/system/templates/js/foundation-5.0.2/js/foundation.min.js"></script>
<script>
    jQuery(document).foundation();
</script>

</body>

</html>
