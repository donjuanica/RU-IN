<?php

header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

class Template {
   public $template;
   function load($filepath) {
      $this->template = file_get_contents($filepath);
   }
   function replace($var, $content) {
      $this->template = str_replace("{".$var."}", $content, $this->template);
   }
   function publish() {
   //die ($this->template);
      eval("?>".$this->template."<?");
   }
}
?>