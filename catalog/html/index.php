<?php
    isset($_GET['page']) ? $page = $_GET['page']: $page='1';
    $per_page = '8';
    $cur_page = ($per_page*$page)-$per_page;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">


    <title>Search</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-7s5uDGW3AHqw6xtJmNNtr+OBRJUlgkNJEo78P4b0yRw= sha512-nNo+yCHEyn0smMxSswnf/OnX6/KwJuZTlNZBjauKhTK0c+zT+q5JOCx0UFhXQ6rJR9jg6Es8gPuD2uZcYDLqSw==" crossorigin="anonymous">
    <link href="../css/custom.css" rel="stylesheet">
  </head>

  <body>
      
    <?php 
    //handling q parameter
    isset($_GET['q']) ? $search = $_GET['q'] : $search = ''; 
    ?>
      
      
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
    //adding library class for searching
    require '../lib/lib.search.php';
    $myDB = new Connection;
    


    
    ?>
   
    <?php if ($search!="") { ?> 
      <div class="container"><H3>Order By Relevancy Query:</H3>search string: <b><?php echo $search; ?></b></div>';
        <?php $rs = $myDB->getSearch($search,'0',$cur_page, $per_page); ?>
        <section class="gray-bg kode-best-sellter-sec">
        	<div class="container">
            	<!--SECTION CONTENT START-->
            	<div class="section-content">
                    <H2><?php echo $rs["TotalString"]; ?> for <?php echo $search; ?></H2>
                </div>
                <!--SECTION CONTENT END-->
                <div class="row masonry-container">
                    
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
                                <p><?php echo $myDB->limitWord($r["topic"],10); ?></p>
                                <p><?php echo $r["author"]; ?></p>
                                <a href="/catalog/details/<?php echo $r["biblio_id"]; ?>" class="add-to-cart">Details</a>
                            </div>
                        </div>
                    </div>
            
                      

                    <?php } //end for $rs["Records"] ?>
                	
                    
				</div>	
            </div>
            <div class='container'>
                   <?php
                    echo $myDB->pagination($rs["Total"],$per_page,$page);
                    ?>
            </div>
        </section>

      
          <?php } //end if search result exist ?>
        


  </body>
  
  
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>


</html>