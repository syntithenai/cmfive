<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" > <![endif]-->
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo ucfirst($w->currentModule()); ?><?php echo!empty($title) ? ' - ' . $title : ''; ?></title>
        <link rel="icon" href="<?php echo WEBROOT; ?>/templates/img/favicon.png" type="image/png"/>

        <?php
        $w->enqueueStyle(array("name" => "normalize.css", "uri" => "/system/templates/js/foundation-5.2.2/css/normalize.css", "weight" => 1010));
        $w->enqueueStyle(array("name" => "foundation.css", "uri" => "/system/templates/js/foundation-5.2.2/css/foundation.css", "weight" => 1005));
        $w->enqueueStyle(array("name" => "style.css", "uri" => "/system/templates/css/style.css", "weight" => 1000));
        $w->enqueueStyle(array("name" => "tablesorter.css", "uri" => "/system/templates/css/tablesorter.css", "weight" => 990));
        $w->enqueueStyle(array("name" => "datePicker.css", "uri" => "/system/templates/css/datePicker.css", "weight" => 980));
        $w->enqueueStyle(array("name" => "jquery-ui-1.8.13.custom.css", "uri" => "/system/templates/js/jquery-ui-new/css/custom-theme/jquery-ui-1.8.13.custom.css", "weight" => 970));
        $w->enqueueStyle(array("name" => "liveValidation.css", "uri" => "/system/templates/css/liveValidation.css", "weight" => 960));
        $w->enqueueStyle(array("name" => "colorbox.css", "uri" => "/system/templates/js/colorbox/colorbox/colorbox.css", "weight" => 950));
        $w->enqueueStyle(array("name" => "jquery.asmselect.css", "uri" => "/system/templates/css/jquery.asmselect.css", "weight" => 940));
        $w->enqueueStyle(array("name" => "foundation-icons.css", "uri" => "/system/templates/font/foundation-icons/foundation-icons.css", "weight" => 930));

        $w->enqueueScript(array("name" => "modernizr.js", "uri" => "/system/templates/js/foundation-5.2.2/js/vendor/modernizr.js", "weight" => 1010));
        $w->enqueueScript(array("name" => "jquery.js", "uri" => "/system/templates/js/foundation-5.2.2/js/vendor/jquery.js", "weight" => 1000));
        $w->enqueueScript(array("name" => "jquery.tablesorter.js", "uri" => "/system/templates/js/tablesorter/jquery.tablesorter.js", "weight" => 990));
        $w->enqueueScript(array("name" => "jquery.tablesorter.pager.js", "uri" => "/system/templates/js/tablesorter/addons/pager/jquery.tablesorter.pager.js", "weight" => 980));
        $w->enqueueScript(array("name" => "jquery.colorbox-min.js", "uri" => "/system/templates/js/colorbox/colorbox/jquery.colorbox-min.js", "weight" => 970));
        $w->enqueueScript(array("name" => "jquery-ui-1.10.4.custom.min.js", "uri" => "/system/templates/js/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js", "weight" => 960));
//        $w->enqueueScript(array("name" => "jquery-ui-1.8.13.custom.min.js", "uri" => "/system/templates/js/jquery-ui-new/js/jquery-ui-1.8.13.custom.min.js", "weight" => 960));
        $w->enqueueScript(array("name" => "jquery-ui-timepicker-addon.js", "uri" => "/system/templates/js/jquery-ui-timepicker-addon.js", "weight" => 950));
        $w->enqueueScript(array("name" => "livevalidation.js", "uri" => "/system/templates/js/livevalidation.js", "weight" => 940));
        $w->enqueueScript(array("name" => "main.js", "uri" => "/system/templates/js/main.js", "weight" => 995));
        $w->enqueueScript(array("name" => "jquery.asmselect.js", "uri" => "/system/templates/js/jquery.asmselect.js", "weight" => 920));
        $w->enqueueScript(array("name" => "boxover.js", "uri" => "/system/templates/js/boxover.js", "weight" => 910));
        $w->enqueueScript(array("name" => "ckeditor.js", "uri" => "/system/templates/js/ckeditor/ckeditor.js", "weight" => 900));
        $w->enqueueScript(array("name" => "Chart.js", "uri" => "/system/templates/js/chart-js/Chart.js", "weight" => 890));

        $w->outputStyles();
        $w->outputScripts();
        ?>
        <script type="text/javascript">
            var $ = jQuery;

            $(document).ready(function() {
//                $(".msg").delay(3000).fadeOut(3000);
//                $(".error").delay(6000).fadeOut(3000);
                $("table.tablesorter").tablesorter({dateFormat: "uk", widthFixed: true, widgets: ['zebra']});
                <?php
                $tab = $w->request('tab');
                if (!empty($tab)) : ?>
                    switchTab("<?php echo $tab; ?>");
                <?php else: ?>

                    $(".tab-head").children("a").each(function() {
                        $(this).bind("click", {alink: this}, function(event) {
                            changeTab(event.data.alink.hash);
                            return false;
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
            <nav class="top-bar" data-topbar><!-- To make it that you need to click to activate dropdown use  data-options="is_hover: false" -->
                <ul class="title-area">
                    <li class="name">
                        <!--<h1><a href="/"><?php // echo str_replace("http://", "", $w->moduleConf('main', 'company_url'));   ?></a></h1>-->
                    </li>
                    <li class="toggle-topbar"><a href="">Menu</a></li>
                </ul>

                <section class="top-bar-section">
                    <!-- Right Nav Section -->
                    <ul class="right">
                        <!-- Search bar -->
                        <li><?php echo Html::box("/search", "<span class='fi-magnifying-glass'></span>", false); ?></li>
                        
                        <!-- User Profile drop down -->
                        <?php if ($w->Auth->user()): ?>
                            <li class="has-dropdown">
                                <a href="#"><span  class="fi-torso"></span></a>
                                <?php
                                echo Html::ul(
                                    array(
                                        $w->menuBox("auth/profile/box", $w->Auth->user()->getShortName()),
                                        $w->menuLink("auth/logout", "Logout")
                                    ), null, "dropdown");
                                ?>    
                            </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Left Nav Section -->
                    <ul class="left">
                        <?php if ($w->Auth->loggedIn()) : ?>
                            <li><?php echo $w->menuLink($w->Main->getUserRedirectURL(), "<span class='fi-home'></span>"); ?></li>
                            <li class="divider"></li>
                            <?php foreach ($w->modules() as $module) {
                                // Check if config is set to display on topmenu
                                if (Config::get("{$module}.topmenu") && Config::get("{$module}.active")) :
                                    // Check for navigation
                                    if (method_exists($module . "Service", "navigation")) : ?>
                                        <li class="has-dropdown <?php echo $w->_module == $module ? 'active' : ''; ?>" id="topnav_<?php echo $module; ?>">
                                            <?php echo $w->menuLink($module . "/index", ucfirst($module)); ?>
                                            <?php echo Html::ul($w->service($module)->navigation($w), null, "dropdown"); ?>
                                        </li>
                                    <?php else: ?>
                                        <li <?php echo $w->_module == $module ? 'class="active"' : ''; ?>><?php echo $w->menuLink($module . "/index", ucfirst($module)); ?></li>
                                    <?php endif; ?>
                                    <li class="divider"></li>
                                <?php endif;
                            }
                        
                            if ($w->Auth->allowed('help/view')) : ?>
                                <li><?php echo Html::box(WEBROOT . "/help/view/" . $w->_module . ($w->_submodule ? "-" . $w->_submodule : "") . "/" . $w->_action, "<span class='fi-q'>?</span>", false, true, 750, 500); ?> </li>
                            <?php endif;
                        endif; ?>
                    </ul> <!-- End left nav section -->
                </section>
            </nav>
        </div>

        <div class="row-fluid" style="overflow: hidden; padding: 10px;">
         <!--<?php /* Check if there are side boxes defined */
            // if (!empty($boxes)) : ?>
                <div class="small-12 medium-2 left">
                    <?php // foreach ($boxes as $btitle => $box) : ?>
                        <div class="row boxes">
                            <h5><?php // echo ucfirst($btitle); ?></h5>
                            <?php // echo $box; ?>
                        </div>
                    <?php // endforeach; ?>
                </div>
            <?php // endif; ?>-->

            <?php // Body section w/ message and body from template ?>
            <div class="body row-fluid <?php // if(!empty($boxes)) echo "medium-10 small-12 "; ?>">
                <h3 class="header"><?php echo !empty($title) ? $title : ucfirst($w->currentModule()); ?></h3>
                <?php if (!empty($error) || !empty($msg)) : ?>
                    <?php $type = !empty($error) ? array("name" => "error", "class" => "warning") : array("name" => "msg", "class" => "info"); ?>
                    <div data-alert class="alert-box <?php echo $type["class"]; ?>">
                        <?php echo $$type["name"]; ?>
                        <a href="#" class="close">&times;</a>
                    </div>
                <?php endif; ?>

                <div class="row-fluid" style="overflow: hidden;">
                    <?php echo !empty($body) ? $body : ''; ?>
                </div>
            </div>
        </div>
        
        <div class="row-fluid">
            <div id="footer">
                Copyright &#169; <?php echo date('Y'); ?>&nbsp;&nbsp;&nbsp;<a href="<?php echo $w->moduleConf('main', 'company_url'); ?>"><?php echo $w->moduleConf('main', 'company_name'); ?></a>
            </div>
        </div>

        <div id="cmfive-modal" class="reveal-modal xlarge" data-reveal></div>
        
        <script type="text/javascript" src="/system/templates/js/foundation-5.2.2/js/foundation.min.js"></script>
        <script>
            $(document).foundation();
            
            var modal_history = [];
            var modal_history_pop = false;
            
            // Automatically append the close 'x' to reveal modals
            $(document).on('opened', '[data-reveal]', function () {
                console.log(modal_history);
                $("#cmfive-modal").append("<a class=\"close-reveal-modal\">&#215;</a>");
                modal_history.push()
                bindModalLinks();
            });
            
            function bindModalLinks() {
                // Stop a links and follow them inside the reveal modal
                $("#cmfive-modal a:not(#modal-back)").click(function(event) {                    
                    if ($(this).hasClass("close-reveal-modal")) {
                        $("#cmfive-modal").foundation("reveal", "close");
                    } else {
                        if ($(this).attr('href')[0] === "#") {
                            return true;
                        } else {
                            // Add href to history if the href wasnt the last item in the stack and that we arent the back link
                            if (modal_history.indexOf($(this).attr('href')) !== modal_history.length) {
                                modal_history.push($(this).attr('href'));
                                modal_history_pop = true;
                            }
                            changeModalWindow($(this).attr('href'));
                        }
                    }
                    return false;
                });
                
                // Bind back traversal to modal window
                $("#cmfive-modal #modal-back").click(function(event) {
                    // event.preventDefault();
                    if (modal_history.length > 0) {
                        // When you click a link, THAT link goes onto the stack.
                        // However we want the one before it.
                        // The modal_history_pop prevents us from popping twice (if back is pressed twice in a row
                        // for example)
                        if (modal_history_pop) {
                            modal_history.pop();
                            modal_history_pop = false;
                        }
                        if (modal_history.length > 0) {
                            changeModalWindow(modal_history.pop());
                        }
                        console.log(modal_history);
                    } 
                    return false;
                });
            }
            
            // Updates the modal window by content from ajax request to uri
            function changeModalWindow(uri) {
                $.get(uri, function(data) {
                    $("#cmfive-modal").html(data + "<a class=\"close-reveal-modal\">&#215;</a>");
                    bindModalLinks();
                });
            }
        </script>

    </body>
</html>
