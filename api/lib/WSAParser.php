<?php
    /**
    * WSAParser class
    * 
    * Basic Class to parse the WSA XML Response and print it to HTML
    * 
    * WebSEOAnalytics.com API: 1.21V
    * 
    */
    class WSAParser {
        
        public static function viewSubscriptions($result) {
            $HTMLoutput='';
            
            ob_start();
            $xml = simplexml_load_string($result); //load XML string
            if($xml->status==1) { //check for errors
                ?>
                <table border="1">
                    <tr>
                        <th>Subscription ID</th>
                        <th>Pack ID</th>
                        <th>Pack Name</th>
                    </tr>
                <?php
                foreach($xml->output->subscriptionList->entry as $subscription) {
                    ?>
                    <tr>
                        <td><?php echo (string)$subscription->subscriptionID; ?></td>
                        <td><?php echo (string)$subscription->packageID; ?></td>
                        <td><?php echo (string)$subscription->packageName; ?></td>
                    </tr>
                    <?php
                }
                ?>
                </table>
            <?php
            }
            else { 
                echo 'ERROR '.$xml->error->ErrorCode.': '.$xml->error->ErrorMessage;
            }
            unset($xml);
            
            $HTMLoutput = ob_get_contents();
            ob_end_clean();
            
            return $HTMLoutput;
        }
        
        public static function deleteReportResponse($result) {
            $HTMLoutput='';
            
            $xml = simplexml_load_string($result); //load XML string
            if($xml->status==1) { //check for errors
                $HTMLoutput='Success';
            }
            else { 
                $HTMLoutput='ERROR '.$xml->error->ErrorCode.': '.$xml->error->ErrorMessage;
            }
            unset($xml);
            
            return $HTMLoutput;
        }
        
        public static function viewListResponse($result) {
            $HTMLoutput='';
            
            ob_start();
            $xml = simplexml_load_string($result); //load XML string
            if($xml->status==1) { //check for errors
                ?>
                <table border="1">
                    <tr>
                        <th>Report ID</th>
                        <th>Datetime</th>
                        <th>Name</th>
                        <th>Version</th>
                        <th>&nbsp;</th>
                    </tr>
                <?php
                foreach($xml->output->reportList->entry as $subscription) {
                    ?>
                    <tr>
                        <td><?php echo (string)$subscription->reportID; ?></td>
                        <td><?php echo (string)$subscription->datetime; ?></td>
                        <td><?php echo (string)$subscription->name; ?></td>
                        <td><?php echo (string)$subscription->version; ?></td>
                        <td><a href="viewReport.php?reportID=<?php echo (string)$subscription->reportID; ?>">View</a> - <a href="covert2PDF.php?reportID=<?php echo (string)$subscription->reportID; ?>">PDF</a> - <a href="deleteReport.php?reportID=<?php echo (string)$subscription->reportID; ?>">Delete</a></td>
                    </tr>
                    <?php
                }
                ?>
                </table>
            <?php
            }
            else { 
                echo 'ERROR '.$xml->error->ErrorCode.': '.$xml->error->ErrorMessage;
            }
            unset($xml);
            
            
            $HTMLoutput = ob_get_contents();
            ob_end_clean();
            
            return $HTMLoutput;
        }
        
        public static function viewLimitsResponse($result) {
            $HTMLoutput='';
            
            ob_start();
            $xml = simplexml_load_string($result); //load XML string
            if($xml->status==1) { //check for errors
                ?>
                <table border="1">
                    <tr>
                        <th>Subscription ID</th>
                        <th>Executed Reports</th>
                        <th>Limit</th>
                        <th>Limit Type</th>
                        <th>Time Interval</th>
                    </tr>
                <?php
                foreach($xml->output->limitsList->entry as $limit) {
                    ?>
                    <tr>
                        <td><?php echo (string)$limit->subscriptionID; ?></td>
                        <td><?php echo (string)$limit->count; ?></td>
                        <td><?php echo (string)$limit->limit; ?></td>
                        <td><?php echo (string)$limit->type; ?></td>
                        <td><?php echo (string)$limit->period; ?></td>
                    </tr>
                    <?php
                }
                ?>
                </table>
            <?php
            }
            else { 
                echo 'ERROR '.$xml->error->ErrorCode.': '.$xml->error->ErrorMessage;
            }
            unset($xml);
            
            
            $HTMLoutput = ob_get_contents();
            ob_end_clean();
            
            return $HTMLoutput;
        }
        
        public static function viewReportResponse($result) {
            $HTMLoutput='';
            
            ob_start();
            $xml = simplexml_load_string($result); //load XML string
            if($xml->status==1) { //check for errors
            
                $toolID=0;
                foreach($xml->output->attributes() as $attrName=>$attrValue) {
                    if($attrName=='toolID') {
                        $toolID=(string)$attrValue;
                    }
                }
                
                $templateFile='';
                if($toolID=='7') { //KEYWORD ANALYZER
                    $templateFile='keyword-analyzer.php';
                }
                else if($toolID=='18') { //KEYWORD BATTLE
                    $templateFile='keyword-battle.php';
                }
                else if($toolID=='14') { //SITE SUBMITTER
                    $templateFile='site-submitter.php';
                }
                else if($toolID=='2') { //DOMAIN INFO
                    $templateFile='domain-info.php';
                }
                else if($toolID=='4') { //INDEXED PAGES
                    $templateFile='indexed-pages.php';
                }
                else if($toolID=='13') { //REPUTATION TRACKER
                    $templateFile='reputation-tracker.php';
                }
                else if($toolID=='8') { //DUPLICATE CONTENT
                    $templateFile='duplicate-content.php';
                }
                else if($toolID=='3') { //HTML VALIDATION
                    $templateFile='html-validator.php';
                }
                else if($toolID=='5') { //LINK POPULARITY
                    $templateFile='link-popularity.php';
                }
                else if($toolID=='6') { //BACKLINK ANALYSIS
                    $templateFile='backlink-analysis.php';
                }
                else if($toolID=='12') { //WEB SEO ANALYSIS
                    $templateFile='web-seo-analysis.php';
                }
                else if($toolID=='20') { //LINK STRUCTURE
                    $templateFile='link-structure.php';
                }
                else if($toolID=='21') { //BACKLINK HUNTER
                    $templateFile='backlink-hunter.php';
                }
                else if($toolID=='9') { //DOMAIN BATTLE
                    $templateFile='domain-battle.php';
                }
                else if($toolID=='10') { //SERP ANALYSIS
                    $templateFile='serp-analysis.php';
                }
                else if($toolID=='11') { //BLOG ANALYZER
                    $templateFile='blog-analyzer.php';
                }
                else if($toolID=='15') { //KEYWORD RESEARCH
                    $templateFile='keyword-research.php';
                }
                else if($toolID=='16') { //KEYWORD DIFFICULTY
                    $templateFile='keyword-difficulty.php';
                }
                else if($toolID=='23') { //AGED DOMAIN FINDER
                    $templateFile='aged-domain-finder.php';
                }
                else if($toolID=='24') { //URL ANALYZER
                    $templateFile='url-analyzer.php';
                }
                
                if($templateFile!='' && file_exists('templates/'.$templateFile)) {
                    include_once('templates/'.$templateFile);
                }
                else {
                    echo 'Unknown SEO Tool';
                }
            }
            else { 
                echo 'ERROR '.$xml->error->ErrorCode.': '.$xml->error->ErrorMessage;
            }
            unset($xml);
            
            $HTMLoutput = ob_get_contents();
            ob_end_clean();
            
            return $HTMLoutput;
        }
    }
?>