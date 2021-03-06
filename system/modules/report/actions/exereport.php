<?php

// criteria/parameter form is submited and report is executed
function exereport_ALL(Web &$w) {
    $w->Report->navigation($w, "Generate Report");
    $p = $w->pathMatch("id");

    $arrreq = array();
    // prepare export buttons for display if format = html
    foreach ($_POST as $name => $value) {
        $arrreq[] = $name . "=" . urlencode($value);
    }

    $viewurl = "/report/viewreport/" . $p['id'];
    $runurl = "/report/runreport/" . $p['id'] . "/?" . implode("&", $arrreq);
    $repurl = "/report/exereport/" . $p['id'] . "?";
    $strREQ = $arrreq ? implode("&", $arrreq) : "";
    $urlcsv = $repurl . $strREQ . "&format=csv";
    $urlpdf = $repurl . $strREQ . "&format=pdf";
    $urlxml = $repurl . $strREQ . "&format=xml";
    $btncsv = Html::b($urlcsv, "Export as CSV");
    $btnpdf = Html::b($urlpdf, "Export as PDF");
    $btnxml = Html::b($urlxml, "Export as XML");
    $btnrun = Html::b($runurl, "Edit Report Parameters");
    $btnview = Html::b($viewurl, "Edit Report");
    $results = "";
    // if there is a report ID in the URL ...
    if ($p['id']) {
        // get member
        $member = $w->Report->getReportMember($p['id'], $w->session('user_id'));

        // get the relevant report
        $rep = $w->Report->getReportInfo($p['id']);

        // if report exists, execute it
        if ($rep) {
            $w->Report->navigation($w, $rep->title);

            // prepare and execute the report
            $tbl = $rep->getReportData();

            // if we have an empty return, say as much
            if (!$tbl) {
                $w->error("No Data found for selections. Please try again....", "/report/runreport/" . $rep->id);
            }
            // if an ERROR is returned, say as much
            elseif ($tbl[0][0] == "ERROR") {
                $w->error($tbl[1][0], "/report/runreport/" . $rep->id);
            }
            // if we have records, present them in the requested format
            else {
                // Below ifs will no longer work
                $request_format = $w->request('format');
                // as a cvs file for download
                if ($request_format == "csv") {
                    $w->setLayout(null);
                    $w->Report->exportcsv($tbl, $rep->title);
                }
                // as a PDF file for download
                elseif ($request_format == "pdf") {
                    $w->setLayout(null);
                    $w->Report->exportpdf($tbl, $rep->title);
                }
                // as XML document for download
                elseif ($request_format == "xml") {
                    $w->setLayout(null);
                    $w->Report->exportxml($tbl, $rep->title);
                }
                // default to a web page
                else {
                    // allowing multiple SQL statements, each returns a recordset as a seperate array element, ie. iterate
                    // array: report parameters > report title > data columns > recordset
                    foreach ($tbl as $t) {
                        
                        $crumbs = array_shift($t);
                        $title = array_shift($t);
                        
                        // first row is our column headings
                        $hds[] = array_shift($t);
                        
                        // first row has column names as associative. change keys to numeric to match recordset
                        $tvalues = array_values($hds[0]);
                        
                        // find key of any links
                        foreach ($tvalues as $h) {
                            if (stripos($h, "_link")) {
                                list($fld, $lnk) = preg_split("/_/", $h);
                                $f = array_search($fld . "_link", $tvalues);
                                $ukey[$f] = $fld;
                                unset($hds[0][$h]);
                            }
                        }

                        if (!empty($ukey)) {
                            // now need to find key of fields to link
                            foreach ($tvalues as $m => $h) {
                                foreach ($ukey as $n => $u) {
                                    if ($u == $h)
                                        $fkey[$n] = array_search($u, $tvalues);
                                }
                            }

                            // iterate row to create link and dump URL related fields
                            foreach ($t as $v) {
                                // keys points to fields so need to maintain array and create all URLS
                                // before we start dumping fields and splicing links
                                foreach ($fkey as $n => $u) {
                                    $a[$n] = "<a href=\"" . $v[$n] . "\">" . $v[$u] . "</a>";
                                    $dump[] = $n;
                                    $dump[] = $u;
                                }


                                // dump url related fields
                                foreach ($dump as $num) {
                                    unset($v[$num]);
                                }

                                // add completed URL(s)
                                foreach ($a as $num => $url) {
                                    $v[$num] = $url;
                                }

                                // we now have gaps from our unsetting and inserting of links
                                // eg. $v[3], $v[4], $v[6], $v[0]
                                // get array_keys into new array:
                                $sortv = array_keys($v);
                                // sort so keys are now in order dispite the gaps
                                sort($sortv);
                                // create new - ordered - array setting our original array values
                                foreach ($sortv as $num => $val) {
                                    $sorted[] = $v[$val];
                                }

                                $arr[] = $sorted;
                                unset($a);
                                unset($dump);
                                unset($sorted);
                            }
                            // recreate $t
                            $t = $arr;
                            unset($ukey);
                            unset($fkey);
                        }
                        // put headings back into array
                        $t = array_merge($hds, $t);

                        // Render selected template
                        $report_template = $w->Report->getReportTemplate($w->request('format'));
                        $template = null;
                        if (empty($report_template->id)) {
                            
                            // Use default
                            $templates = $w->Template->findTemplates('report', 'default');
                            $template = $templates[0];
                            if (empty($template->id)) {
                                $results .= "<b>" . $title . "</b>" . Html::table($t, null, "tablesorter", true);
//                                $w->error("Report template not found", "/report/editreport/{$p['id']}");
                            }
                        } else {
                        
                            $results .= "<h3 class='subheader'>" . $title . "</h3>" . $w->Template->render(
                                    !empty($report_template->template_id) ? $report_template->template_id : ($template->id), 
                                    array("data" => $t)
                                );
                        }
                        // build results table
                        // 
                        
                        // reset parameters string
                        $strcrumb = "";
                        unset($hds);
                        unset($arr);
                    }

                    // display export and function buttons
//                    $w->ctx("exportxml", $btnxml);
//                    $w->ctx("exportcsv", $btncsv);
//                    $w->ctx("exportpdf", $btnpdf);
                    $w->ctx("btnrun", $btnrun);
                    $w->ctx("showreport", $results);

                    // allow editor/admin to edit the report
                    if ((!empty($member->role) && $member->role == "EDITOR") || ($w->Auth->user()->hasRole("report_admin"))) {
                        $w->ctx("btnview", $btnview);
                    }
                }
            }
        } else {
            // report does not exist?
            $w->ctx("showreport", "No such report?");
        }
    }
}
