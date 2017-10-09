<?php
    class TestParser {
        protected $page;
        protected $img_tpath; 

        public function getPage($url) {
            $html = '';
            $html = file_get_contents_proxy($url);
            return $html;
        }

        public function saveImg() {
            //$id = ;
            $catalog = substr(md5($id), 0, 1).'/';
            $uploaddir = '..'.$this->img_tpath.'/'.$catalog;  
            if( ! is_dir( $uploaddir ) ) mkdir( $uploaddir, 0777 );
            $file_name = $id.'.jpg';
            // сохраняем на диск
            $res = file_put_contents($uploaddir.$file_name, file_get_contents_proxy($url));   
        }

        public function find() {
            $dom = new DomDocument();
            @$dom->loadHTML($html); 
            $xpath = new DomXPath($dom);

            $nodes = $xpath->query(".//*[contains(@class, 'lead')]");  
            foreach ($nodes as $i => $node) {
                $text = $node->nodeValue;
            }  

            $nodes = $xpath->query(".//*[contains(@class, 'img-ttl')]/img");
            foreach ($nodes as $i => $node) {
                $img_scr = $node->getAttribute('src');
            } 
            return $text;
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