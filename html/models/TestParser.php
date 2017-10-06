<?php
    class TestParser {
        protected $page;

        public function getPage($url) {
            $html = '';
            $html = file_get_contents_proxy($url);
            return $html;
        }

        public function saveImg() {
            
        }

        public function find() {
            $dom = new DomDocument();
            @$dom->loadHTML($html); 
            $xpath = new DomXPath($dom);

            //$nodes = $xpath->query($expression);  
            //foreach ($nodes as $i => $node) {
            //}  

            $nodes = $xpath->query(".//*[contains(@class, 'img')]/img");
            foreach ($nodes as $i => $node) {
                $src = $node->nodeValue;
            } 
            return $src;
        }   

        public function has($html, $expression) {
            $dom = new DomDocument();
            @$dom->loadHTML($html); 
            $xpath = new DomXPath($dom);
            $expression = sprintf('count(%s) > 0', $expression);
            return $xpath->evaluate($expression);
        }  
    };
?>