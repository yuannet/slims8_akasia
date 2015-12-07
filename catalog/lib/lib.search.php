<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// key to authenticate
define('INDEX_AUTH', '1');
require '../../sysconfig.inc.php';


class Connection{

    protected $db;

    public function Connection(){

    $conn = NULL;

        try{
            $dsn = 'mysql:dbname='.DB_NAME.';charset=utf8;host='.DB_HOST;
            $user = DB_USERNAME;
            $password = DB_PASSWORD;
            
            $conn = new PDO($dsn, $user, $password); 
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e){
                echo 'ERROR: ' . $e->getMessage();
                }    
            $this->db = $conn;
    }
    
    public function getConnection(){
        return $this->db;
    }
    
    public function close(){
        return $this->db = null;
        
    }
    public function getRecent($limit) {
        $db = $this->getConnection();
        $sql = 'SELECT SQL_CALC_FOUND_ROWS biblio.biblio_id, biblio.title, biblio.image, '
        . 'biblio.isbn_issn, biblio.publish_year, pbl.publisher_name AS `publisher`, '
        . 'pplc.place_name AS `publish_place`, biblio.labels, biblio.input_date '
        . 'FROM biblio '
        . 'LEFT JOIN biblio_author ba ON biblio.biblio_id = ba.biblio_id '
        . 'LEFT JOIN mst_author ma ON ba.author_id = ma.author_id '
        . 'LEFT JOIN biblio_topic bt ON biblio.biblio_id = bt.biblio_id '
        . 'LEFT JOIN mst_topic mt ON bt.topic_id = mt.topic_id '
        . 'LEFT JOIN mst_publisher AS pbl ON biblio.publisher_id=pbl.publisher_id '
        . 'LEFT JOIN mst_place AS pplc ON biblio.publish_place_id=pplc.place_id '
        . 'GROUP BY biblio.biblio_id '
        . 'ORDER BY biblio.input_date DESC '
        . 'LIMIT 0,:limit';
        
        //wihtin LIMIT we must use (int) to make suervalue is integer
        $limit = (int)$limit;
        $query = $db->prepare($sql);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
        public function getSearchFlat($search,$exclude_biblio_id='?',$start=0,$limit=10) {
        //adding plus befor each word;
        $search = '+'.$search;
        $search = str_replace(",", "", $search);
        $search = str_replace(" ", " +", $search);
        $search = htmlentities($search);
        //exlude own biblio_id  @getRelate
        if (!is_null($exclude_biblio_id)) {
            $exclude_biblio_id ='AND biblio_id NOT IN ('. $exclude_biblio_id.')';
        }
       
        //get PDO Connection
        $db = $this->getConnection();        
        
        $sql = 'SELECT SQL_CALC_FOUND_ROWS biblio_id, title, image, '
        . 'isbn_issn, publish_year, publisher, '
        . 'publish_place, labels, input_date, author, topic, '
        . 'MATCH (title) AGAINST (:search) SCORE1, '
        . 'MATCH (topic) AGAINST (:search) SCORE2, '
        . 'MATCH (author) AGAINST (:search) SCORE3, '
        . 'MATCH (notes) AGAINST (:search) SCORE4 '
        . 'FROM search_biblio '
        . 'WHERE (MATCH (title) AGAINST (:search) '
        . 'OR MATCH (author) AGAINST (:search) '
        . 'OR MATCH (topic) AGAINST (:search) '
        . 'OR MATCH (notes) AGAINST (:search)) '
        . $exclude_biblio_id
        . ' GROUP BY biblio_id '
        . 'ORDER BY (SCORE1)+(SCORE2*3)+(SCORE3*2)+(SCORE4*0.5) DESC, biblio_id DESC '
        . 'LIMIT :start,:limit';
        
        $sqlstring = str_replace(":search","'".$search."'",$sql);
        $sqlstring = str_replace(":start",$start,$sqlstring);
        $sqlstring = str_replace(":limit",$limit,$sqlstring);
        echo '<div class="container thumbnail">'.$sqlstring.'</div>';
        $start = (int)$start;
        $limit = (int)$limit;
        $query = $db->prepare($sql);
            
        $query->bindParam(':start', $start, PDO::PARAM_INT);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->bindParam(':search', $search, PDO::PARAM_STR);
        $query->execute();        
        $total = $db->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN);
        $total > 1 ? $found = ' Results' : $found = ' Result';
        $getSearch["Total"] =  $total;
        $getSearch["TotalString"] =  $total.$found;
        $getSearch["Records"] = $query->fetchAll(PDO::FETCH_ASSOC);
        
        return $getSearch;
        
        
        
    
    }
    
    //
    public function getAdsense($search,$limit=100,$total_return=1) {
        
        $adsense = $this->getSearchFlat($search,'0',0,$limit);
        
        $adsense = $adsense["Records"]; 
        
        $name = 'input_date';
        usort($adsense, function ($a, $b) use(&$name){
            return strtotime($b[$name]) - strtotime($a[$name]);});
        $adsense = array_slice($adsense, 0, $total_return);
        
        return $adsense;
    }
    
    
    public function getSearch($search,$exclude_biblio_id='?',$start=0,$limit=10) {
        //adding plus befor each word;
        $search = '+'.$search;
        $search = str_replace(",", "", $search);
        $search = str_replace(" ", " +", $search);
        $search = htmlentities($search);
        //exlude own biblio_id  @getRelate
        if (!is_null($exclude_biblio_id)) {
            $exclude_biblio_id ='AND biblio.biblio_id NOT IN ('. $exclude_biblio_id.')';
        }
       
        //get PDO Connection
        $db = $this->getConnection();

        $sql = 'SELECT SQL_CALC_FOUND_ROWS biblio.biblio_id, biblio.title, biblio.image, '
        . 'biblio.isbn_issn, biblio.publish_year, pbl.publisher_name AS `publisher`, '
        . 'pplc.place_name AS `publish_place`, biblio.labels, biblio.input_date, '
        . 'GROUP_CONCAT(DISTINCT(ma.author_name) SEPARATOR ", ") author, '
        . 'GROUP_CONCAT(DISTINCT(mt.topic) SEPARATOR ", ") topic, '
        . '(MATCH (title) AGAINST (:search))'
        . '+SUM(MATCH (author_name) AGAINST (:search))*2'
        . '+SUM(MATCH (topic) AGAINST (:search))*3 '
        . '+ (MATCH (notes) AGAINST (:search))*0.5 SCORE '
        . 'FROM biblio '
        . 'LEFT JOIN biblio_author ba ON biblio.biblio_id = ba.biblio_id '
        . 'LEFT JOIN mst_author ma ON ba.author_id = ma.author_id '
        . 'LEFT JOIN biblio_topic bt ON biblio.biblio_id = bt.biblio_id '
        . 'LEFT JOIN mst_topic mt ON bt.topic_id = mt.topic_id '
        . 'LEFT JOIN mst_publisher AS pbl ON biblio.publisher_id=pbl.publisher_id '
        . 'LEFT JOIN mst_place AS pplc ON biblio.publish_place_id=pplc.place_id '
        . 'WHERE (MATCH (title) AGAINST (:search) '
        . 'OR MATCH (author_name) AGAINST (:search) '
        . 'OR MATCH (topic) AGAINST (:search) '
        . 'OR MATCH (notes) AGAINST (:search)) '
        . $exclude_biblio_id
        . ' GROUP BY biblio.biblio_id '
        . 'ORDER BY SCORE DESC, biblio.biblio_id DESC '
        . 'LIMIT :start,:limit';
        
        $sqlstring = str_replace(":search","'".$search."'",$sql);
        $sqlstring = str_replace(":start",$start,$sqlstring);
        $sqlstring = str_replace(":limit",$limit,$sqlstring);
        echo '<div class="container thumbnail">'.$sqlstring.'</div>';
        $start = (int)$start;
        $limit = (int)$limit;
        $query = $db->prepare($sql);
            
        $query->bindParam(':start', $start, PDO::PARAM_INT);
        $query->bindParam(':limit', $limit, PDO::PARAM_INT);
        $query->bindParam(':search', $search, PDO::PARAM_STR);
        $query->execute();        
        $total = $db->query('SELECT FOUND_ROWS();')->fetch(PDO::FETCH_COLUMN);
        $total > 1 ? $found = ' Results' : $found = ' Result';
        $getSearch["Total"] =  $total;
        $getSearch["TotalString"] =  $total.$found;
        $getSearch["Records"] = $query->fetchAll(PDO::FETCH_ASSOC);
        
        return $getSearch;
        
        
        
    
    }
    
    public function getDetails($biblio_id) {
        $db = $this->getConnection();
        $sql = 'SELECT SQL_CALC_FOUND_ROWS biblio.biblio_id, biblio.title, biblio.image, '
        . 'biblio.isbn_issn, biblio.publish_year, pbl.publisher_name AS `publisher`, '
        . 'pplc.place_name AS `publish_place`, biblio.labels, biblio.input_date, '
        . 'GROUP_CONCAT(DISTINCT(mt.topic) SEPARATOR ", ") topic '
        . 'FROM biblio '
        . 'LEFT JOIN biblio_author ba ON biblio.biblio_id = ba.biblio_id '
        . 'LEFT JOIN mst_author ma ON ba.author_id = ma.author_id '
        . 'LEFT JOIN biblio_topic bt ON biblio.biblio_id = bt.biblio_id '
        . 'LEFT JOIN mst_topic mt ON bt.topic_id = mt.topic_id '
        . 'LEFT JOIN mst_publisher AS pbl ON biblio.publisher_id=pbl.publisher_id '
        . 'LEFT JOIN mst_place AS pplc ON biblio.publish_place_id=pplc.place_id '
        . 'WHERE biblio.biblio_id = :biblio_id '
        . 'GROUP BY biblio.biblio_id '
        . 'ORDER BY biblio.input_date DESC '
        . 'LIMIT 0,1';
        
        $query = $db->prepare($sql);
        $query->bindParam(':biblio_id', $biblio_id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    //to getRelated record, get the details id and query the title
    public function getRelate($biblio_id,$limit) {
       $relate = $this->getDetails($biblio_id);
       !empty($relate[0]["topic"])? $search = $relate[0]["topic"]: $search = $relate[0]["title"];
       $exclude_biblio_id = $biblio_id;
       $relate = $this->getSearchFlat($search,$exclude_biblio_id,0,$limit);
       return $relate; 
    }
    
    
    
    function pagination($total=100,$per_page = 10,$page = 1, $url = '?'){  
        //source: http://www.awcore.com/dev/1/3/Create-Awesome-PHPMYSQL-Pagination_en#toggle
        $adjacents = "2"; 
            $total=$total;
        $page = ($page == 0 ? 1 : $page);  
        $start = ($page - 1) * $per_page;                               
         
        $prev = $page - 1;                          
        $next = $page + 1;
        $lastpage = ceil($total/$per_page);
        $lpm1 = $lastpage - 1;
         
        $pagination = "";
        if($lastpage > 1)
        {   
            $pagination .= "<ul class='pagination'>";
                   // $pagination .= "<li class='details'>Page $page of $lastpage</li>";
            if ($lastpage < 7 + ($adjacents * 2))
            {   
                for ($counter = 1; $counter <= $lastpage; $counter++)
                {
                    if ($counter == $page)
                        $pagination.= "<li><a class='current'>$counter</a></li>";
                    else
                        $pagination.= "<li><a href='{$url}page=$counter'>$counter</a></li>";                    
                }
            }
            elseif($lastpage > 5 + ($adjacents * 2))
            {
                if($page < 1 + ($adjacents * 2))     
                {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++)
                    {
                        if ($counter == $page)
                            $pagination.= "<li><a class='current'>$counter</a></li>";
                        else
                            $pagination.= "<li><a href='{$url}page=$counter'>$counter</a></li>";                    
                    }
                    $pagination.= "<li class='dot'>...</li>";
                    $pagination.= "<li><a href='{$url}page=$lpm1'>$lpm1</a></li>";
                    $pagination.= "<li><a href='{$url}page=$lastpage'>$lastpage</a></li>";      
                }
                elseif($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2))
                {
                    $pagination.= "<li><a href='{$url}page=1'>1</a></li>";
                    $pagination.= "<li><a href='{$url}page=2'>2</a></li>";
                    $pagination.= "<li class='dot'>...</li>";
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++)
                    {
                        if ($counter == $page)
                            $pagination.= "<li><a class='current'>$counter</a></li>";
                        else
                            $pagination.= "<li><a href='{$url}page=$counter'>$counter</a></li>";                    
                    }
                    $pagination.= "<li class='dot'>..</li>";
                    $pagination.= "<li><a href='{$url}page=$lpm1'>$lpm1</a></li>";
                    $pagination.= "<li><a href='{$url}page=$lastpage'>$lastpage</a></li>";      
                }
                else
                {
                    $pagination.= "<li><a href='{$url}page=1'>1</a></li>";
                    $pagination.= "<li><a href='{$url}page=2'>2</a></li>";
                    $pagination.= "<li class='dot'>..</li>";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++)
                    {
                        if ($counter == $page)
                            $pagination.= "<li><a class='current'>$counter</a></li>";
                        else
                            $pagination.= "<li><a href='{$url}page=$counter'>$counter</a></li>";                    
                    }
                }
            }
             
            if ($page < $counter - 1){ 
                $pagination.= "<li><a href='{$url}page=$next'>Next</a></li>";
                $pagination.= "<li><a href='{$url}page=$lastpage'>Last</a></li>";
            }else{
                $pagination.= "<li><a class='current'>Next</a></li>";
                $pagination.= "<li><a class='current'>Last</a></li>";
            }
            $pagination.= "</ul>\n";      
        }
     
     
        return $pagination;
    } 
    
    function limitWord($text, $limit) {
          if (str_word_count($text, 0) > $limit) {
              $words = str_word_count($text, 2);
              $pos = array_keys($words);
              $text = substr($text, 0, $pos[$limit]) . '...';
          }
          return $text;
        }

}

