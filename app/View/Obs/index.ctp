
<div class="row main-panel" style="margin-top:10px;">
    <div id="right-main" class="col-lg-3 col-md-6 ui-sortable">
        <div class="panel panel-default" id="project-list">
            <div class="panel-heading panel-blue info-upper-panel p-hide">פרויקטים</div>
            <ul id="projects-body" class="registered-users-list clearfix p-hide">
             
            </ul>
            <!-- /ul -->  
            
            <div class="panel-footer clearfix p-hide">
                <div class="icon pull-left">
                    <button id="projects-left" type="button" disabled class="btn btn-default btn-xs pull-left">
                         <i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true"></i>
                    </button> 
                </div> 
                
                <div id="footer-project" class="footer-index text-center">
                    
                </div>  
                
                <div class="icon pull-right">
                    <button id="projects-right" type="button" class="btn btn-default btn-xs pull-right">
                        <i class="fa fa-arrow-circle-right fa-2x" aria-hidden="true"></i>

                    </button> 
                </div>   
              
            </div>
            <!-- /.panel-footer -->    
        </div> 
        <!-- /#project-list -->
        
        <div class="panel panel-default" id="user-list">
            <div class="panel-heading panel-green info-upper-panel u-hide">
                <div class="header-title">
                    משתמשים
                </div>
                <div id="user-helpers">
                    <div class="check-container" >
                        <input type="checkbox" name="user-checkbox" id="user-checkbox" class="css-checkbox" disabled>
                        <label for="user-checkbox" class="css-label">
                            <i class="fa fa-eye-slash" data-toggle="tooltip" title="משתמש נבחר" aria-hidden="true"></i>
                            <i class="fa fa-eye" data-toggle="tooltip" title="כל המשתמשים" aria-hidden="true"></i>
                        </label>
                    </div>
                    <input id="user-search" type="search" data-toggle="tooltip" title="חפש משתמש" placeholder="חפש מתצפת">
                </div>
            </div>
                <ul id="users-body" class="registered-users-list clearfix u-hide">
                
                </ul>
                <!-- /ul -->

                <div class="panel-footer u-hide">
                    <div class="icon pull-left">
                        <button type="button" disabled id="users-left" class="btn btn-default btn-xs pull-left">
                            <i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true"></i>
                        </button> 
                    </div>    
                    
                    <div id="footer-user" class="footer-index text-center">
                
                    </div> 
                    
                    <div class="icon pull-right">
                        <button type="button" id="users-right" class="btn btn-default btn-xs pull-right">
                            <i class="fa fa-arrow-circle-right fa-2x" aria-hidden="true"></i>
                        </button> 
                    </div>   
                    <div class="clearfix"></div>

                </div>
                <!-- /.panel-footer -->
        </div>
        <!-- /#user-list -->        
    </div>
    <!-- /.col-lg-3 -->
    
    <div class="col-lg-6" id="main-resize">
        <div class="row" id="row-sums"> 
            <div class="panel panel-default" id="sums-panel">
                <div class="panel-heading">  
                    <div class="row"> 
                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-blue" data-toggle="tooltip" data-placement="up" title="פרוייקטים">
                                <div class="panel-heading ">
                                    <div class="row">
                                        <div class="col-xs-9 text-right">
                                            <div id="sProject" class="info-upper-panel" ></div>
                                        </div>
                                        <div class="col-xs-3">
                                            <i class="fa fa-picture-o fa-1x"></i>
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                            <!-- /.panel-blue -->
                        </div>
                        <!-- /.col-lg-3 -->
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-green" data-toggle="tooltip" data-placement="up" title="משתמשים">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-xs-9 text-right">
                                            <div id="sUsers" class="info-upper-panel"></div>
                                        </div>
                                        <div class="col-xs-3">
                                            <i class="fa fa-user fa-1x"></i>
                                        </div>
                                    </div>
                                </div>                                                            
                            </div>
                            <!-- /.panel-green -->
                        </div>
                        <!-- /.col-lg-3 -->
                        
                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-yellow" data-toggle="tooltip" data-placement="up" title="זנים">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-xs-9 text-right">
                                            <div id="sTaxons" class="info-upper-panel"></div>
                                        </div>
                                        <div class="col-xs-3">
                                            <i class="fa fa-tripadvisor fa-1x"></i>
                                        </div>
                                    </div>
                                </div>   
                            </div>
                             <!-- /.panel-yellow -->
                        </div>
                        <!-- /.col-lg-3 -->

                        <div class="col-lg-3 col-md-6">
                            <div class="panel panel-red" data-toggle="tooltip" data-placement="up" title="תצפיות">
                                <div class="panel-heading">
                                    <div class="row">
                                        <div class="col-xs-9 text-right">
                                            <div id="sObservations" class="info-upper-panel"></div>
                                        </div>
                                        <div class="col-xs-3">
                                            <i class="fa fa-map-marker fa-1x"></i>
                                        </div>
                                    </div>
                                </div>                               
                            </div>
                            <!-- /.panel-red -->
                        </div>
                        <!-- /.col-lg-3 -->
                    </div>
                    <!-- /.row -->
                </div> 
                <!-- /.panel-heading -->
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.row -->

        <div class="row" id="row-main">     
            <div  class="panel panel-default" id="main-panel-container">
                <div class="panel-body" id="main-panel">
        
                        <ul class="nav nav-tabs" id="tabs">
                           <li id="graph-tab" class="active"><a class="main-tab" href="#" data-toggle="tab" >גרפים</a></li>
                           <li id="map-tab"><a class="main-tab" href="#" data-toggle="tab">מפות</a></li>
                       </ul> 
                        <button id="resize-btn" class="btn btn-default" data-toggle="tooltip" title="הרחב/קבץ" type="button">
                           <i class="fa fa-expand fa-1x" aria-hidden="true"></i>
                        </button>
                       

                        <ul id="seassions">
                            <li id="summer"><a href="#"><img src="assets/images/summer.png"></a></li>
                            <li id="autumn"><a href="#"><img src="assets/images/autumn.png"></a></li>
                            <li id="winter"><a href="#"><img src="assets/images/winter.png"></a></li>
                            <li id="spring"><a href="#"><img src="assets/images/spring.png"></a></li>
                       </ul>  
                        
                      
                        <div class="in" id="graphs">
                            <div class="graph-title">
                                    <div class="dropdown" style="display: inline">
                                       <button id="graph-btn" class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">גרפים
                                            <span class="caret"></span>
                                       </button>
                                       <ul id="action_menu" class="dropdown-menu">                            

                                       </ul>
                                   </div> 
                                   <div class="left-center">
                                       <i style="vertical-align: middle;" class="fa fa-bar-chart-o fa-fw"></i>
                                       <span style="vertical-align: middle;">ניתוח גרפי</span>
                                   </div>                             
                           </div>
                           <!-- /.panel-heading -->
                           <div id="graph-container"> 
                               
                           </div>                                                                      
                        </div>
                        <div class="out" id="maps">
                                                
                        </div>
                </div>                                                        
                  
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.row -->

    </div>
    <!-- /.col-lg-6 -->

    <div id="left-main" class="col-lg-3 col-md-6 ui-sortable">
        <div  class="panel panel-default" id="taxa-list" >

                <div class="panel-heading panel-yellow info-upper-panel t-hide">
                    <div class="header-title">
                        זנים
                    </div>
                    <div id="taxon-helpers">
                        <div class="check-container" >
                            <input type="checkbox" name="taxon-checkbox" id="taxon-checkbox" class="css-checkbox" disabled>
                            <label for="taxon-checkbox" class="css-label">
                                <i class="fa fa-eye-slash" data-toggle="tooltip" title="זן נבחר" aria-hidden="true"></i>
                                <i class="fa fa-eye" data-toggle="tooltip" title="כל הזנים" aria-hidden="true"></i>
                            </label>
                        </div>
                        <input id="taxon-search" type="search" data-toggle="tooltip" title="חפש זן" placeholder="חפש זן">
                    </div>

                     <div class="clearfix"></div>
                    <hr style ="width:100%; margin-top: 0px; margin-bottom: 9px;">
                    <ul class="nav nav-pills" id="ani-pills" style="cursor: initial; text-align:center;">
                        <li id="All" class="li-pills" data-toggle="tooltip" data-placement="up" title="כל הזנים"><a href="#" class="ani-tab"><i class="fa fa-globe" aria-hidden="true"></i></a></li>
                        <li id="Mammalia" class="li-pills" data-toggle="tooltip" data-placement="up" title="יונקים"><a href="#" class="ani-tab"><i class="gicon-uni41 mammals" aria-hidden="true"></i></a></li>
                        <li class="li-pills" id="Aves" data-toggle="tooltip" data-placement="up" title="עופות"><a href="#" class="ani-tab"><i class="gicon-uni43 birds"></i></a></li>
                        <li class="li-pills" id="Reptilia" data-toggle="tooltip" data-placement="up" title="זוחלים"><a href="#" class="ani-tab"><i class="gicon-y rep"></i></a></li>
                        <li class="li-pills" id="Arachnida" data-toggle="tooltip" data-placement="up" title="פרוקי רגליים"><a href="#" class="ani-tab"><i class="gicon-m arachnida"></i></a></li>
                        <li class="li-pills" id="Insecta" data-toggle="tooltip" data-placement="up" title="חרקים"><a href="#" class="ani-tab"><i class="gicon-uni114 insects"></i></a></li>
                        <li class="li-pills" id="Plantae" data-toggle="tooltip" data-placement="up" title="צמחים"><a href="#" class="ani-tab"><i class="gicon-G2 plants"></i></a></li>
                        <li class="li-pills" id="Amphibia" data-toggle="tooltip" data-placement="up" title="דו-חיים"><a href="#" class="ani-tab"><i class="gicon-uni12D"></i></a></li>
                        <li class="li-pills" id="Animalia" data-toggle="tooltip" data-placement="up" title="חיות"><a href="#" class="ani-tab"><i class="gicon-uni10A"></i></a></li>
                        <li class="li-pills" id="Fungi" data-toggle="tooltip" data-placement="up" title="פטריות"><a href="#" class="ani-tab"><i class="gicon-A2"></i></a></li>
                        <li class="li-pills" id="Mollusca" data-toggle="tooltip" data-placement="up" title="רכיכות"><a  href="#" class="ani-tab"><i class="gicon-two"></i></a></li>
                    </ul>

                    <div class="clearfix"></div>
                </div>
                <ul id="taxa-body" class="registered-users-list clearfix t-hide">

                </ul>

                <div class="panel-footer t-hide">
                    <div class="icon pull-left">
                        <button id="taxons-left" type="button" disabled class="btn btn-default btn-xs pull-left">
                             <i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true"></i>
                        </button> 
                    </div>    

                    <div id="footer-taxon" class="footer-index text-center">

                    </div>                 

                    <div class="icon pull-right">
                        <button id="taxons-right" type="button" class="btn btn-default btn-xs pull-right">
                            <i class="fa fa-arrow-circle-right fa-2x" aria-hidden="true"></i>
                        </button> 
                    </div>   
                    <div class="clearfix"></div>
                </div>
            </div>
            <!-- /#taxa-list -->       
    </div>            
    <!-- /.col-lg-3 -->



    
</div>
<!-- /.row -->

    <div class="row" style="margin-top:15px; margin-left: 0; margin-right: 0; height: 60px;">
        <div class="panel panel-default" id="main-footer">
            <div class="col-lg-3 uni"><img src='assets/images/uni.png'></div>
            <div class="col-lg-6 department">החוג למערכות מידע</div>
            <div class="col-lg-3 rights"><span >נוצר על ידי: אבנר אינוז</span></div>
        </div>  

    </div>
    <!-- /.row -->

<script>
   
   
    var projectsGlobal = '<?php echo json_encode($projects); ?>';
  
    
</script>

<script>
var statsByProject = {
ajaxUrl : '<?php echo $this->Html->url("/json/obs/byproject"); ?>'
};
</script>

<script>   
var statsByProjectIcon = {
ajaxUrl : '<?php echo $this->Html->url("/json/obs/byprojectIcon"); ?>'
};
</script>

<script>
var statsByUserIcon = {
ajaxUrl : '<?php echo $this->Html->url("/json/obs/byuserIcon"); ?>'
};
</script>

<script>
var statsByUserAll = {
ajaxUrl : '<?php echo $this->Html->url("/json/obs/byuserAll"); ?>'
};
</script>

<script>
var statsByTaxon = {
ajaxUrl : '<?php echo $this->Html->url("/json/obs/bytaxon"); ?>'
};
</script>

<script>
var statsByTaxonAndUser = {
ajaxUrl : '<?php echo $this->Html->url("/json/obs/bytaxonanduser"); ?>'
};
</script>

<script>
var statsBySeassions = {
ajaxUrl : '<?php echo $this->Html->url("/json/obs/byseassions"); ?>'
};
</script>

