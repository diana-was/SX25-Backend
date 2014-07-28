<?php
  /**
  * WSAclient class
  * 
  * Basic Class to call WSA API
  * 
  * WebSEOAnalytics.com API: 1.21V
  * 
  */
  class WSAclient {
      const Version='1.2';
      
      private $userID;
      private $apiKey;
      
      /**
       * Make an HTTP request
       *
       * @return API results
       */
      private function callWebService($actionHandler,$POSTparameters) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.webseoanalytics.com/api/'.WSAclient::Version.'/'.$actionHandler);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTparameters);

        $result = curl_exec ($ch);
       /*
       echo 'URL:  http://www.webseoanalytics.com/api/'.WSAclient::Version.'/'.$actionHandler;
       print_r($POSTparameters);
       echo 'Status :'.curl_getinfo($ch,CURLINFO_HTTP_CODE).'<br>';
       echo 'Url :'.curl_getinfo($ch,CURLINFO_EFFECTIVE_URL).'<br>';
       echo 'Error :'.curl_errno( $ch ).'<br>';
       echo 'Message :'.curl_error( $ch ).'<br>';
       echo 'Info : <pre>'; print_r(curl_getinfo($ch)); echo '</pre><br>';
       echo 'Page :'.$result.'<br>';
       */
        curl_close ($ch);
        unset($ch);
        
        return $result;
      }
      
      /**
       * Create the Auth Key
       */
      private function createAuthKey($userID,$apiKey,$timestamp) {
          $string2sign = $userID."\n".$timestamp;
          $authKey = base64_encode(hash_hmac('sha1',$string2sign,$apiKey,true));
          return $authKey;
      }
      
      /**
       * Initialize the Request Parameters
       */
      private function initParams($format,$lang,$jsonCallback='') {
          if(function_exists('date_default_timezone_set')) {
                date_default_timezone_set('GMT');
          }
          
          $timestamp=time();
          
          $authKey=$this->createAuthKey($this->userID,$this->apiKey,$timestamp);
          
          $POSTparameters=array(
            'userID'=>$this->userID,
            'authKey'=>$authKey,
            'timestamp'=>$timestamp,
            'format'=>$format,
            'lang'=>$lang,
          );
          
          if($format=='json' && $jsonCallback!='') {
              $POSTparameters['callback']=$jsonCallback;
          }
          
          return $POSTparameters;
      }
      
      /**
       * construct WSAclient object
       */
      public function __construct($userID,$apiKey) {
          $this->userID=$userID;
          $this->apiKey=$apiKey;
      }
      
      /**
       * call the viewSubscriptions Action
       *
       * @return API results
       */
      public function viewSubscriptions($format,$lang,$jsonCallback='') {
          $POSTparameters=$this->initParams($format,$lang,$jsonCallback='');
          return $this->callWebService('viewSubscriptions.php',$POSTparameters);
      }
      
      /**
       * call the deleteReport Action
       *
       * @return API results
       */
      public function deleteReport($reportID,$subscriptionID,$format,$lang,$jsonCallback='') {
          $POSTparameters=$this->initParams($format,$lang,$jsonCallback='');
          $POSTparameters['reportID']=$reportID;
          $POSTparameters['subscriptionID']=$subscriptionID;
          return $this->callWebService('deleteReport.php',$POSTparameters);
      }
      
      /**
       * call the viewReport Action
       *
       * @return API results
       */
      public function viewReport($reportID,$subscriptionID,$format,$lang,$jsonCallback='') {
          $POSTparameters=$this->initParams($format,$lang,$jsonCallback='');
          $POSTparameters['reportID']=$reportID;
          $POSTparameters['subscriptionID']=$subscriptionID;
          return $this->callWebService('viewReport.php',$POSTparameters);
      }
      
      /**
       * call the viewList Action
       *
       * @return API results
       */
      public function viewList($toolID,$subscriptionID,$format,$lang,$jsonCallback='') {
          $POSTparameters=$this->initParams($format,$lang,$jsonCallback='');
          $POSTparameters['toolID']=$toolID;
          $POSTparameters['subscriptionID']=$subscriptionID;
          return $this->callWebService('viewList.php',$POSTparameters);
      }
      
      /**
       * call the viewLimits Action
       *
       * @return API results
       */
      public function viewLimits($toolID,$subscriptionID,$format,$lang,$jsonCallback='') {
          $POSTparameters=$this->initParams($format,$lang,$jsonCallback='');
          $POSTparameters['toolID']=$toolID;
          $POSTparameters['subscriptionID']=$subscriptionID;
          return $this->callWebService('viewLimits.php',$POSTparameters);
      }
      
      /**
       * call the newReport Action
       *
       * @return API results
       */
      public function newReport($toolID,$toolParamArray,$subscriptionID,$format,$lang,$jsonCallback='') {
          $POSTparameters=$this->initParams($format,$lang,$jsonCallback='');
          $POSTparameters['toolID']=$toolID;
          $POSTparameters['subscriptionID']=$subscriptionID;
          foreach($toolParamArray as $paramName=>$paramValue) {
              $POSTparameters[$paramName]=$paramValue;
          }
          return $this->callWebService('newReport.php',$POSTparameters);
      }
  }
?>
