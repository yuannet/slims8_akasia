
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">


    <title>Search</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-7s5uDGW3AHqw6xtJmNNtr+OBRJUlgkNJEo78P4b0yRw= sha512-nNo+yCHEyn0smMxSswnf/OnX6/KwJuZTlNZBjauKhTK0c+zT+q5JOCx0UFhXQ6rJR9jg6Es8gPuD2uZcYDLqSw==" crossorigin="anonymous">
    <link href="../css/custom.css" rel="stylesheet">
  </head>

  <body>

      
      
    <div class="container margin-search-box">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">

                <form class="form-horizontal" action="/catalog/search/" onsubmit="return false;">
                    <div id="imaginary_container"> 
                        <div class="input-group stylish-input-group">
                            <input type="text" class="form-control input-lg"  placeholder="Search" name="q">
                            <span class="input-group-addon">
                               <!-- removing query string question mark -->
                               <button type="submit" onclick="window.location.href=this.form.action + this.form.q.value;" >
                                    <span class="glyphicon glyphicon-search"></span>
                                </button>  
                            </span>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <?php
    isset($_GET['id']) ? $id = $_GET['id']: $id='';

    //adding library class for searching
    require '../lib/lib.search.php';
    $myDB = new Connection;
    ?>
      <?php    $rs = $myDB->getDetails($id);    
      ?>   
      
      <div class="container margin-40 ">
          <div class="col-md-4">
              <img src='../image/300/<?php echo $rs[0]["image"]; ?>'>
          </div>
          <div class="col-md-8">
              <H1><?php echo $rs[0]["title"]; ?></H1>
              <p>Keywords: <?php echo $rs[0]["topic"]; ?></p>
          </div>
      </div>
      <div class="container"><H3>Related Collections Query: </H3>
      search string: 
      <?php 
      !empty($rs[0]["topic"]) ? $string = '<b>Keywords Field-></b>'.$rs[0]["topic"]: $string = '<b>Title Field-></b>'. $rs[0]["title"]; 
      echo $string;
      ?>
      </div>
      <?php $rs = $myDB->getRelate($id,4); ?>
        <section class="gray-bg kode-best-sellter-sec">
        	<div class="container">
            	<!--SECTION CONTENT START-->
            	<div class="section-content">
                    
                    <H2>Related Collections</H2>
                </div>
                <!--SECTION CONTENT END-->
              
                <div class="row">
                      
                    <?php  foreach ($rs["Records"] as $r) { ?>
                    
                    
                    
                    <div class="col-md-3">
                    	<div class="best-seller-pro">
                        	<figure>
                               <img src='../image/200/<?php echo $r["image"]; ?>'>
                            </figure>
                            <div class="kode-text">
                            	<h3><?php echo $myDB->limitWord($r["title"],5); ?></h3> 
                            </div>
                            <div class="kode-caption">
                            	<h4><?php echo $r["title"]; ?></h4>
                                <p><?php echo $r["topic"]; ?></p>
                                <p><?php echo $r["author"]; ?></p>
                                <a href="/catalog/details/<?php echo $r["biblio_id"]; ?>" class="add-to-cart">Details</a>
                            </div>
                        </div>
                    </div>
            
                      

                    <?php } //end for $rs["Records"] ?>
                	
                    
				</div>	
            </div>
            <div class='container'>
             
            </div>
        </section>
        
        
        
        

      
      
   
        
        

  </body>
  
  
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>


</html>