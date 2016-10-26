/* global statsByProject, Highcharts, taxasGlobal, projectsGlobal, usersGlobal, currentUserGlobal, currentProjectGlobal, currentTaxonGlobal, observationsGlobal, statsByUser, moment, ObsByDate, ObsByDateAndTaxon, statsByTaxon, statsByTaxonAndUser, statsAjax, google, statsByProjectIcon, statsByUserAll, statsByUserIcon, statsBySeassions */

$(function () {
  var tabOn = 0 ;                   //indicate if tab graph = 0 or tab map =1 is on. default graph
  var resizeOn = 0;                 // indicate if we are in resize mode of map and graph. off = 0 , on = 1
  var map;
  var userS = $("#user-search");
  var taxonS = $("#taxon-search");
  var users = $("#users-body");
  var taxas = $("#taxa-body");
  var projects = $("#projects-body");
  var pills = $("#ani-pills li");
  var seassions = $("#seassions li");
  var groups ;                      // getting the first taxon data from view
  var filterd_projects = JSON.parse(projectsGlobal);          // getting the first projects data from view
  var filterd_users ;               // getting the first users data from view
  var filterd_obs ;                // getting the first users data from view
  var filterd_taxons;
  var current_user ;
  var current_project;
  var current_taxon;
  var user_jump = 0;
  var project_jump = 0;
  var taxon_jump = 0;
  var graph;
  var selectedProject;    // varible to hold the selected project pill to pressent related data
  var selectedPill = $("#All");                             // varible to hold the selected icon pill to pressent related taxa
  var selectedSeassion = null;
  var selectedProject = 4527;    // varible to hold the selected project pill to pressent related data
   var selectedUsers = [],
      selectedTaxs = [],
      selectedUsersObs = [],
      selectedUsersTaxonObs = [],
      selectedProjectObs = [],
      selectedProjectTaxonObs = [],
      selectedTaxonsUsersObs = [],
      selectedSeassionsObs = []; 
  
    _init_map();
  
  
  function _refresh_panels()
  {
      if (selectedTaxs === undefined || selectedTaxs.length === 0)
            current_taxon = +_repaint_taxons(0) - +1;
        else{
           update_current_taxon();
           current_taxon += _repaint_taxons(current_taxon) - +1;
        }
             
        if (filterd_taxons[+current_taxon + +1] === undefined)
           $("#taxons-right").attr("disabled", true);
        else
           $("#taxons-right").attr("disabled", false);
         if (filterd_taxons[current_taxon-(current_taxon%20)] === undefined || (current_taxon-(current_taxon%20))===0 )   
             $("#taxons-left").attr("disabled", true);
        else
             $("#taxons-left").attr("disabled", false);
         
        $('#taxa-body').removeClass('loading');
                                              
       if (selectedUsers === undefined || selectedUsers.length === 0)
            current_user = +_repaint_users(0) - +1;
        else{
           update_current_user();
           current_user += _repaint_users(current_user) - +1;
        }
             
        if (filterd_users[+current_user + +1] === undefined)
           $("#users-right").attr("disabled", true);
        else
           $("#users-right").attr("disabled", false);
         if (filterd_users[current_user-(current_user%8)] === undefined || (current_user-(current_user%8))===0 )   
             $("#users-left").attr("disabled", true);
        else
             $("#users-left").attr("disabled", false);
         
        $('#users-body').removeClass('loading');
        
        if ($('#taxa-list').hasClass('open'))
        {
            $('.u-hide').hide();
            $('.p-hide').hide();
        }else if ($('#user-list').hasClass('open'))
        {
            $('.t-hide').hide();
            $('.p-hide').hide();            
        }
        else if ($('#project-list').hasClass('open'))
        {
            $('.t-hide').hide();
            $('.u-hide').hide();            
        }
  }
  
  var refreshDataByProject = function(){
           
      _repaint_projects(0);
      var options = {
      url :  statsByProject.ajaxUrl,
      dataType: 'json',
      method: 'POST',
      async: false,
      data : {
        project: selectedProject,
        searchUser: userS.val(),
        searchTaxon: taxonS.val()
      }
    };
       
    $.ajax(options).done(function (results) {
       
        $('#projects-body').removeClass('loading'); 
       
        selectedTaxs=[];
        selectedUsers=[];
        $(selectedSeassion).removeClass('active');
        selectedSeassion = null;        
        filterd_users = results.users;
        filterd_obs = results.observations;
        filterd_taxons = results.taxons;     
        
      _repaint_sums(null,null,null);
        
        current_user = +_repaint_users(0) - +1;
        if (filterd_users[+current_user + +1] === undefined)
           $("#users-right").attr("disabled", true);
        else
           $("#users-right").attr("disabled", false);

        $("#users-left").attr("disabled", true);
       
        $('#users-body').removeClass('loading');
                  
        $(selectedPill).removeClass('active');
        selectedPill = $("#All");
        selectedPill.addClass('active');
        
        current_taxon = +_repaint_taxons(0) - +1;
        if (filterd_taxons[+current_taxon + +1] === undefined)
           $("#taxons-right").attr("disabled", true);
        else
           $("#taxons-right").attr("disabled", false);

        $("#taxons-left").attr("disabled", true);       
      
        $('#taxa-body').removeClass('loading');
    
    if ($('#taxa-list').hasClass('open'))
        {
            $('.u-hide').hide();
            $('.p-hide').hide();
        }else if ($('#user-list').hasClass('open'))
        {
            $('.t-hide').hide();
            $('.p-hide').hide();            
        }
        else if ($('#project-list').hasClass('open'))
        {
            $('.t-hide').hide();
            $('.u-hide').hide();            
        }    

     groups = results.groups;
     selectedProjectTaxonObs = results.ObsByDateAndTaxon;
     selectedProjectObs = results.ObsByDate;
      if (tabOn ===1)
      {
         $('#maps').removeClass('out');
         $('#maps').addClass('in');
      }     
      _update_map();


    });      
    };
 
 
  var refreshDataByProjectIcon = function(){

      _repaint_projects(0);
      var options = {
      url :  statsByProjectIcon.ajaxUrl,
      dataType: 'json',
      method: 'POST',
      async: false,
      data : {
        project: selectedProject,
        searchUser: userS.val(),
        searchTaxon: taxonS.val(),
        pill: selectedPill.attr('id')
      }
    };
       
    $.ajax(options).done(function (results) {
                  
        $('#projects-body').removeClass('loading'); 
       
        filterd_taxons = results.taxons;
        filterd_obs = results.observations;       
        filterd_users = results.users;
        
        _repaint_sums(null,null,null);
        
        _refresh_panels();
                       
     selectedProjectObs = results.ObsByDate;
     selectedProjectTaxonObs = results.obsByTaxons;
     if (tabOn ===1)
      {
         $('#maps').removeClass('out');
         $('#maps').addClass('in');
      }
      if (selectedSeassion !== null)
          _update_map_for_seassions();
      else
         _update_map(); 
      

    });      
    };    
    

  var refreshDataByUserIcon = function(){
      
  
    var options = {
      url : statsByUserIcon.ajaxUrl ,
      dataType: 'json',
      method: 'POST',
      async: false,
      data : {
        users: selectedUsers,
        project: selectedProject,
        pill: selectedPill.attr('id')
      }
    };
     
    $.ajax(options).done(function (results) {

        _repaint_projects(0);
        $('#projects-body').removeClass('loading');
 
        filterd_users = results.users;
        filterd_taxons = results.taxons;
        filterd_obs = results.observations;        
        
        _repaint_sums(1,null,null);
        
        _refresh_panels();
        
     selectedUsersTaxonObs = results.obsByTaxons;
     selectedUsersObs = results.ObsByDate; 
     
     if (tabOn ===1)
      {
         $('#maps').removeClass('out');
         $('#maps').addClass('in');
      }
      
      if (selectedSeassion !== null)
          _update_map_for_seassions();
      else
         _update_map(); 
     
    });     
    };
    
    
  var refreshDataByUserAll = function(){
      
    var options = {
      url : statsByUserAll.ajaxUrl ,
      dataType: 'json',
      method: 'POST',
      async: false,
      data : {
        users: selectedUsers,
        project: selectedProject,
        pill: selectedPill.attr('id')
      }
    };
     
    $.ajax(options).done(function (results) {

        _repaint_projects(0);
        $('#projects-body').removeClass('loading');
 
        filterd_users = results.users;
        filterd_taxons = results.taxons;
        filterd_obs = results.observations;        
        groups = results.Iconics;
        
        _repaint_sums(1,results.userTax,results.userObs);
        
        _refresh_panels();
        
     selectedUsersTaxonObs = results.ObsByDateAndTaxon;
     selectedUsersObs = results.ObsByDate; 
     
     if (tabOn ===1)
      {
         $('#maps').removeClass('out');
         $('#maps').addClass('in');
      }
      
      if (selectedSeassion !== null)
          _update_map_for_seassions();
      else
         _update_map(); 
     
    });     
    };

  var refreshDataByTaxon = function(){

    var options = {
      url : statsByTaxon.ajaxUrl,
      dataType: 'json',
      method: 'POST',
      async: false,
      data : {
        project: selectedProject,
        taxas: selectedTaxs,
        pill: selectedPill.attr('id')
      }
    };
     
    $.ajax(options).done(function (results) {
        
        _repaint_projects(0);
        $('#projects-body').removeClass('loading');
        
      filterd_users = results.users;
      filterd_obs = results.observations;
      filterd_taxons = results.taxons;
      
      _repaint_sums(null,selectedTaxs.length,null);
        
      _refresh_panels();
        
      selectedTaxonsUsersObs = results.obsByTaxons;
     
     if (tabOn ===1)
      {
         $('#maps').removeClass('out');
         $('#maps').addClass('in');
      }
      
      if (selectedSeassion !== null)
          _update_map_for_seassions();
      else
         _update_map(); 
     
     
    }); 
     
    };

  var refreshDataByTaxonAndUser = function(){
       
    var options = {
      url : statsByTaxonAndUser.ajaxUrl, 
      dataType: 'json',
      method: 'POST',
      async: false,
      data : {
        users: selectedUsers,
        project: selectedProject,
        taxas: selectedTaxs,
        pill: selectedPill.attr('id')
      }
    };
     
    $.ajax(options).done(function (results) {
      
        _repaint_projects(0);
         $('#projects-body').removeClass('loading');   
         
        filterd_users = results.users;
        filterd_obs = results.observations;
        filterd_taxons = results.taxons;
        
        selectedTaxs = [];
        for (var tax in results.obsByDate) 
             selectedTaxs.push(parseInt(tax));
         
        _repaint_sums(1,selectedTaxs.length,null);
        
        _refresh_panels();
        
         selectedTaxonsUsersObs = results.obsByDate;
          groups = results.obsByTaxons.Iconics;
          
         if (tabOn ===1)
        {
           $('#maps').removeClass('out');
           $('#maps').addClass('in');
        }
        
        if (selectedSeassion !== null)
          _update_map_for_seassions();
        else
         _update_map(); 
        
    }); 
     
    };
  
    var updateSeassions = function(){

    var options = {
      url : statsBySeassions.ajaxUrl,
      dataType: 'json',
      method: 'POST',
      async: false,
      data : {
        users: selectedUsers,
        project: selectedProject,
        taxas: selectedTaxs,
        pill: selectedPill.attr('id')              
      }
    };
     
    $.ajax(options).done(function (results) {
        selectedSeassionsObs = results;
    }); 
     
    };  
    
    
  pills.click(function(){
    var element = $(this);
           
    if(!element.hasClass('active')){
        element.addClass('active');
        $(selectedPill).removeClass('active');
        selectedPill = element; 
        _selectMenu();
        
        if (check_taxon_empty()){
           $('#taxon-checkbox').prop('checked', false);
           $('#taxon-checkbox').attr("disabled", true);
      }         
       else{
           $('#taxon-checkbox').attr("disabled", false);            
           $('#taxon-checkbox').prop('checked', true);
       }                    
    }  
   });     
   
   
   
  projects.on('click','.media',{},function(){ 
 
    userS.val("");
    taxonS.val("");
    var element = $(this);
    var projectID = $(this).data('projectId');
    
    if(!element.hasClass("selected")){     
      projects.find("[data-project-id='" + selectedProject + "']").removeClass('selected');     
      element.addClass('selected');
      selectedProject = projectID;  
      selectedProjectObs = [];
      selectedProjectTaxonObs = []; 
      selectedUsers = [];
      selectedTaxs = [];
    }else{
        $(selectedPill).removeClass('active');
        selectedPill = $("#All");
        $(selectedPill).addClass('active');              
    }
    
    _selectMenu();

  });

  users.on('click','.media',{},function(){
    userS.val("");
    taxonS.val("");
   
   
    var userID = $(this).data('userId');
    var element = $(this);

    if(!element.hasClass("selected")){
      users.find("[data-user-id='" + selectedUsers.shift() + "']").removeClass('selected');     
      element.addClass('selected');
      selectedUsers.push(userID);  
      selectedUsersObs = [];
      selectedUsersTaxonObs = [];     
     }
     else
     {
         element.removeClass('selected');
         selectedUsers.shift();
     }
     
      if (selectedUsers === undefined || selectedUsers.length === 0){
          $('#user-search').show();
           $('#user-checkbox').prop('checked', false);
           $('#user-checkbox').attr("disabled", true);
      }         
       else{
           $('#user-search').hide();
           $('#user-checkbox').attr("disabled", false);            
           $('#user-checkbox').prop('checked', true);
       }                 
      _selectMenu();  
     
  });
  
  taxas.on('click','.media',{},function(){

    userS.val("");
    taxonS.val("");
    
    var taxaID = $(this).data('taxaId');
    var element = $(this);

    if(element.hasClass("selected")){
      element.removeClass('selected');
      selectedTaxs.splice( selectedTaxs.indexOf( taxaID ) , 1);
    }else {
     if (selectedTaxs.length < 5)
     {
        selectedTaxs.push(taxaID);
        element.addClass('selected');
      }else
      {
          alert('ניתן לבחור עד 5 מינים');
          return false;
      }
    }
     
      if (selectedTaxs === undefined || selectedTaxs.length === 0){
          $('#taxon-search').show();
      }         
       else{
           $('#taxon-search').hide();
       }                  
      
     if (check_taxon_empty()){
           $('#taxon-checkbox').prop('checked', false);
           $('#taxon-checkbox').attr("disabled", true);
      }         
       else{
           $('#taxon-checkbox').attr("disabled", false);            
           $('#taxon-checkbox').prop('checked', true);
       }               
       
       
    _selectMenu();

  });
  
   seassions.click(function(){
    var element = $(this);
           
    if(!element.hasClass('active')){
        element.addClass('active');
        if (selectedSeassion)
        {
            $(selectedSeassion).removeClass('active');
          
        }    
          selectedSeassion = element; 
        
       
        seassionObservations();
        _update_map_for_seassions();
    }else
    {
         $(selectedSeassion).removeClass('active');
         selectedSeassion = null;
        //  selectedSeassionsObs = []; 
         _repaint_sums(null,null,null);
         _selectMenuAfterSeassions();
         _update_map(); 
    }
    
      
       
       
   });     
  
  userS.keyup(function(){

    _selectMenu();
});


  userS.focus(function(){
     
    taxonS.val("");
});

  taxonS.keyup(function(){

    _selectMenu();
});

  taxonS.focus(function(){
     userS.val("");

});

    function update_current_user()
    {
       for (var key in filterd_users) {
            var obj = filterd_users[key];
            if (selectedUsers[0]===parseInt(obj.users.id)){               
                current_user = (+key + +1)-((+key + +1)%8);
               break;
           }
       }
    }
    
    function update_current_taxon()
    {
       for (var key in filterd_taxons) {
            var obj = filterd_taxons[key];
            if($.inArray(parseInt(obj.taxons.id), selectedTaxs)>-1)   {           
                current_taxon = (+key + +1)-((+key + +1)%20);
               return false;
           }
       }
       current_taxon = 0;
    }
    
    function check_taxon_empty()
    {
        var isEmpty = true;
        for (var key in filterd_taxons) {
                var obj = filterd_taxons[key];
                if($.inArray(parseInt(obj.taxons.id), selectedTaxs)>-1)   {           
                    isEmpty = false;              
                }
            }  
        return isEmpty;
    }

  //------------------------------------ Handle Users panel left and right -----------------------------------------

   $("#users-left").click(function(){
        var element = $(this);   
        if ( filterd_users[+current_user - +user_jump] !== undefined ) {
            current_user = +current_user - +user_jump + +1;
            user_jump = _repaint_users(+current_user - +8);
            current_user--;

            if ( filterd_users[+current_user - +8] === undefined )
                element.attr("disabled", true);
            else
                element.attr("disabled", false);
            if ( filterd_users[+current_user + +1] === undefined )
                $("#users-right").attr("disabled", true);
            else
                $("#users-right").attr("disabled", false);
        }
    });

    $("#users-right").click(function(){
        var element = $(this);
        if ( filterd_users[+current_user + +1] !== undefined ) {
            user_jump = _repaint_users(+current_user + +1);
            current_user = +current_user + +user_jump;
            if ( filterd_users[+current_user + +1] === undefined )
                element.attr("disabled", true);
            else
                element.attr("disabled", false);
            if ( filterd_users[+current_user - +user_jump] === undefined )
                $("#users-left").attr("disabled", true);
            else
                $("#users-left").attr("disabled", false);       
        }  
    });
    
    function _repaint_users(start)
    {
     
     $("#users-body").empty(); 
     for(var i = 0 ; i < 8 ; i++ )
     {
        item = filterd_users[+start + +i];
        if (!item)
        {
            
            if (i===0)
            {
                $("#users-body").append("<p style=\"font-weight: bold; color: red;\">אין משתמשים לתצוגה</p>");
                $("#footer-user").empty();
                return i;
            }    
            var from = +start + +1;
            var to = +start + +i;           
            $("#footer-user").empty();
            $("#footer-user").append("מציג " + from + " עד "  + to );
            return i;
        }   
        var userImg = item.users.icon;      
        if(!userImg)
                userImg = 'assets/images/persons.png';
        var userRole = item.users.role;
        var hebRole;  
        if (userRole)
        {
            if (userRole === "manager")
                hebRole="תפקיד : מנהל";
            else if (userRole === "curator")
                hebRole="תפקיד : אוצר";
        }else
            hebRole="&nbsp";
        
       $("#users-body").append("<li class=\"media\" style=\"margin-top:0;\" data-user-id=\""+ item.users.id +"\">"
        + "<a href=\"javascript:;\"><img src=\""+ userImg + "\"></a>"
        + "<h1 class=\"username text-ellipsis truncate-name\">" + item.users.login + ""
        + "<small>תצפיות : "+ item[0].sumObs +"</small>"
        + "<small>" + hebRole + "</small>"
        + "</h1>"
        + "</li>");

        if($.inArray(parseInt(item.users.id), selectedUsers)>-1)
            $("#users-body").find("[data-user-id='" + item.users.id + "']").addClass('selected'); 
      }  
      $("#users-body").fadeIn('fast');
      
      var from = +start + +1;
      var to = +start + +8;
    
      $("#footer-user").empty();
      $("#footer-user").append("מציג " + from + " עד "  + to );
      
      return 8;
     }
 
   //------------------------------------ Handle Projects panel left and right -----------------------------------------   
    
    $("#projects-left").click(function(){
        var element = $(this);   
        if ( filterd_projects[+current_project - +project_jump] !== undefined ) {
            current_project = +current_project - +project_jump + +1;
            project_jump = _repaint_projects(+current_project - +4);
            current_project--;

            if ( filterd_projects[+current_project - +4] === undefined )
                element.attr("disabled", true);
            else
                element.attr("disabled", false);
            if ( filterd_projects[+current_project + +1] === undefined )
                $("#projects-right").attr("disabled", true);
            else
                $("#projects-right").attr("disabled", false);
        }
    });

    $("#projects-right").click(function(){
        var element = $(this);
        if ( filterd_projects[+current_project + +1] !== undefined ) {
            project_jump = _repaint_projects(+current_project + +1);
            current_project = +current_project + +project_jump;
            if ( filterd_projects[+current_project + +1] === undefined )
                element.attr("disabled", true);
            else
                element.attr("disabled", false);
            if ( filterd_projects[+current_project - +project_jump] === undefined )
                $("#projects-left").attr("disabled", true);
            else
                $("#projects-left").attr("disabled", false);       
        }  
    });
    
    function _repaint_projects(start)
    {
      $("#projects-right").attr("disabled", true);   
     $("#projects-body").empty(); 
     for(var i = 0 ; i < 4 ; i++ )
     {
    
        var item = filterd_projects[+start + +i];
        if (!item)
        {  
            
            
            var from = +start + +1;
            var to = +start + +i;
            $("#footer-project").empty();
            $("#footer-project").append("מציג " + from + " עד " + to );
            return i;
        }
        var projectImg = item.icon_url;      
        if(!projectImg)
                projectImg = 'assets/images/no-pic.jpg';
        
        var obCount = item.observations_count;  
        if(!obCount)
            obCount = 0;

        var taxCount = item.taxons_count;  
        if(!taxCount)
            taxCount = 0;
        
        var userCount = item.users_count;  
        if(!userCount)
            userCount = 0;       
        
        
       $("#projects-body").append("<li class=\"media\" style=\"margin-top:0;\" data-project-id=\""+ item.id +"\">"
        + "<a href=\"javascript:;\"><img src=\""+ projectImg + "\"></a>"
        + "<h1 class=\"username text-ellipsis\">" + item.slug + ""
        + "<small>תצפיות : "+ obCount +"</small>"
        + "<small>זנים : "+ taxCount +"</small>"
        + "<small>משתמשים : "+ userCount +"</small>"
        + "</h1>"
        + "</li>");

        if(parseInt(item.id)=== selectedProject){
            $("#projects-body").find("[data-project-id='" + item.id + "']").addClass('selected'); 
          
        }
      }  
        
        
        
            var from = +start + +1;
            var to = +start + +4;
            $("#footer-project").empty();
            $("#footer-project").append("מציג " + from + " עד " + to );
      
      $("#projects-body").fadeIn('slow');
      return 4;
     }
     
    //------------------------------------ Handle taxons panel left and right -----------------------------------------    
  
    $("#taxons-left").click(function(){
        
        var element = $(this);   
        if ( filterd_taxons[+current_taxon - +taxon_jump] !== undefined ) {
            current_taxon = +current_taxon - +taxon_jump + +1;
            taxon_jump = _repaint_taxons(+current_taxon - +20);
            current_taxon--;

            if ( filterd_taxons[+current_taxon - +20] === undefined )
                element.attr("disabled", true);
            else
                element.attr("disabled", false);
            if ( filterd_taxons[+current_taxon + +1] === undefined )
                $("#taxons-right").attr("disabled", true);
            else
                $("#taxons-right").attr("disabled", false);
        }        
        
    });

    $("#taxons-right").click(function(){
        var element = $(this);
        if ( filterd_taxons[+current_taxon + +1] !== undefined ) {
            taxon_jump = _repaint_taxons(+current_taxon + +1);
            current_taxon = +current_taxon + +taxon_jump;
            if ( filterd_taxons[+current_taxon + +1] === undefined )
                element.attr("disabled", true);
            else
                element.attr("disabled", false);
            if ( filterd_taxons[+current_taxon - +taxon_jump] === undefined )
                $("#taxons-left").attr("disabled", true);
            else
                $("#taxons-left").attr("disabled", false);       
        }  
    });
        
   function _repaint_taxons(start) 
   { 
     $("#taxa-body").empty(); 
     for(var i = 0 ; i < 20 ; i++ )
     {
        item = filterd_taxons[+start + +i];
        if (!item)
        {
            if (i===0)
            {
                $("#taxa-body").append("<p style=\"font-weight: bold; color: red;\">אין זנים לתצוגה</p>");
                $("#footer-taxon").empty();
                return i;
            } 
            var from = +start + +1;
            var to = +start + +i;           
            $("#footer-taxon").empty();
            $("#footer-taxon").append("מציג " + from + " עד "  + to );
            return i;
        }   
        var taxaImg = item.taxons.photo;

        if(!taxaImg)
                taxaImg = 'assets/images/persons.png';

       $("#taxa-body").append("<li class=\"media\" style=\"margin-top:0;\" data-taxa-id=\""+ item.taxons.id +"\">"
        + "<a href=\"javascript:;\"><img src=\""+ taxaImg + "\"></a>"
        + "<h1 class=\"username text-ellipsis truncate-taxa\">" + item.taxons.name + ""
        + "</h1>"
        + "</li>");
        
        if($.inArray(parseInt(item.taxons.id), selectedTaxs)>-1){
                 $("#taxa-body").find("[data-taxa-id='" + item.taxons.id + "']").addClass('selected'); 
           }
      }  
      
      $("#taxa-body").fadeIn('fast');
      
      var from = +start + +1;
      var to = +start + +20;
    
      $("#footer-taxon").empty();
      $("#footer-taxon").append("מציג " + from + " עד "  + to );
      
      return 20;   
    
   }
  
   //-------------------------------------------- Handle the sums bars ------------------------------------------------ 
    
    function _repaint_sums(users,taxs,observations)  
    {

      
         $("#sProject").empty();
         $("#sProject").append(1);
        
        $("#sUsers").empty();
         if (users !== null)
              $("#sUsers").append(users);
         else
            $("#sUsers").append(filterd_users.length);
        
        $("#sTaxons").empty();
         if (taxs !== null)
              $("#sTaxons").append(taxs); 
         else
             $("#sTaxons").append(filterd_taxons.length);  
     
        
        $("#sObservations").empty();
         if (observations !== null)
               $("#sObservations").append(observations);
         else
             $("#sObservations").append(filterd_obs.length);

    }
     
    //-------------------------------------------- Handle the menu graph -----------------------------------------
      
   function _selectMenu(){
        startLoader();
       if ($(selectedUsers).length===0 && $(selectedTaxs).length===0 && selectedPill.attr('id')==="All")
       {
            graphFlag = 6;
            updateSeassions();
            refreshDataByProject();
            _paintFirstMenuGraph();
            menuFlag = 1;            
            graphSelector();
         
       }else if ($(selectedUsers).length===0 && $(selectedTaxs).length===0 && selectedPill.attr('id')!=="All")
       {
            updateSeassions();
           refreshDataByProjectIcon();
            
           _paintSeventhMenuGraph();
           if (menuFlag===7)
           {
                graphSelector();
            }else if (selectedSeassion!==null)
            {
                menuFlag = 7;
                seassionObservations();
                $('#main-panel-container').removeClass('loading');
            }
            else{
                menuFlag = 7;
                pieIconByObservations();
                $('#main-panel-container').removeClass('loading');
            }               
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length===0 && selectedPill.attr('id')==="All")
       {
            updateSeassions();
           refreshDataByUserAll();
            
           _paintSecondMenuGraph();
           if (menuFlag===2)
           {
                graphSelector();
            }else if (selectedSeassion!==null)
            {
           
               menuFlag = 2;
                seassionObservations();
                $('#main-panel-container').removeClass('loading');
            }
            else{
                menuFlag = 2;
                pieIconByObservations();
                $('#main-panel-container').removeClass('loading');
            }
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length===0 && selectedPill.attr('id')!=="All")
       {
            updateSeassions();
           refreshDataByUserIcon();
            
           _paintEightMenuGraph();
           if (menuFlag===8)
           {
                graphSelector();
            }else if (selectedSeassion!==null)
            {
                menuFlag = 8;
                seassionObservations();
                $('#main-panel-container').removeClass('loading');
            }
            else{
                menuFlag = 8;
                pieIconByObservations();
                $('#main-panel-container').removeClass('loading');
            }
       }else if ($(selectedUsers).length===0 && $(selectedTaxs).length===1)
       {
           updateSeassions();
           refreshDataByTaxon();          
           _paintThirdMenuGraph(); 
            if (menuFlag===3)
            {               
               graphSelector();
            }else if (selectedSeassion!==null)
            {
                menuFlag = 3;
                seassionObservations();
                $('#main-panel-container').removeClass('loading');
            }
            else{
                 menuFlag = 3;
                 donutTaxonDrillUsersObs();
                  $('#main-panel-container').removeClass('loading');
            }
           
       }else if ($(selectedUsers).length===0 && $(selectedTaxs).length>1)
       {
           updateSeassions(); 
           refreshDataByTaxon();
            
           _paintFourthMenuGraph();
           if (menuFlag===4)
           {
                graphSelector();
            }else if (selectedSeassion!==null)
            {
                menuFlag = 4;
                seassionObservations();
                $('#main-panel-container').removeClass('loading');
            }
            else
            {
               menuFlag = 4;
               donutTaxonDrillUsersObs();
                $('#main-panel-container').removeClass('loading');
            }
         
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length===1)
       {
            updateSeassions();
           refreshDataByTaxonAndUser();
            
           _paintFifthMenuGraph();
            if (menuFlag===5)
            {
                graphSelector(); 
            }else if (selectedSeassion!==null)
            {
                menuFlag = 5;
                seassionObservations();
                $('#main-panel-container').removeClass('loading');
            }
            else{
               menuFlag = 5;
               timeByObservations();
                $('#main-panel-container').removeClass('loading');
           }
          
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length>1)
       {
           updateSeassions();
           refreshDataByTaxonAndUser();           
           _paintSixthMenuGraph();
           if (menuFlag===6){
                graphSelector();
            }else if (selectedSeassion!==null)
            {
                menuFlag = 6;
                seassionObservations();
                $('#main-panel-container').removeClass('loading');
            }
           else{
                menuFlag = 6;
                pieTaxByUser();
                $('#main-panel-container').removeClass('loading');
            }          
       }
     
   }
    
   function _selectMenuAfterSeassions(){

       if ($(selectedUsers).length===0 && $(selectedTaxs).length===0 && selectedPill.attr('id')==="All")
       {
            graphFlag = 6;          
           graphSelector();
         
       }else if ($(selectedUsers).length===0 && $(selectedTaxs).length===0 && selectedPill.attr('id')!=="All")
       {
                pieIconByObservations();       
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length===0 && selectedPill.attr('id')==="All")
       {
                pieIconByObservations();
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length===0 && selectedPill.attr('id')!=="All")
       {
                pieIconByObservations();
       }else if ($(selectedUsers).length===0 && $(selectedTaxs).length===1)
       {
                 donutTaxonDrillUsersObs();
       }else if ($(selectedUsers).length===0 && $(selectedTaxs).length>1)
       {
               donutTaxonDrillUsersObs();
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length===1)
       {
               timeByObservations();
       }else if ($(selectedUsers).length===1 && $(selectedTaxs).length>1)
       {
                pieTaxByUser();        
       }
     
   }        
 //-------------------------------------------- Create the html for the graph menues for each possible option ------------------------------------------------    
    
    function _paintFirstMenuGraph(){
        
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>פאי</a>"
            +  "<ul class=\"dropdown-menu\">"         
                  +  "<li class=\"action_li\" data-func=\"pieIconByObservations\"><a href=\"#\">תצפיות לזן</a></li>"       
                  +  "<li class=\"action_li\" data-func=\"pieIconByTax\"><a href=\"#\">התפלגות הזנים</a></li>"
            +  "</ul>"
      +  "</li>"
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>עמודות</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"barTaxonsByObservations\"><a href=\"#\">התפלגות התצפיות לפי זן</a></li>"
            +  "</ul>"
      +  "</li>"  
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"timeByObservations\"><a href=\"#\">התפלגות התצפיות על פני זמן</a></li>"  
                +  "<li class=\"action_li\" data-func=\"timeByTaxonObservations\"><a href=\"#\">התפלגות התצפיות לזן על פני זמן</a></li>"
      +  "</li>"             
      
        );
        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     }); 
    }
 
    function _paintSeventhMenuGraph(){
        
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>פאי</a>"
            +  "<ul class=\"dropdown-menu\">"         
                  +  "<li class=\"action_li\" data-func=\"pieIconByObservations\"><a href=\"#\">תצפיות לזן</a></li>"       
            +  "</ul>"
      +  "</li>"
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>עמודות</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"barTaxonsByObservations\"><a href=\"#\">התפלגות התצפיות לפי זן</a></li>"
            +  "</ul>"
      +  "</li>"  
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"timeByObservations\"><a href=\"#\">התפלגות התצפיות על פני זמן</a></li>"  
            +  "</ul>"
      +  "</li>"             
      
        );
        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     }); 
    }
    
    function _paintSecondMenuGraph(){
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>פאי</a>"
            +  "<ul class=\"dropdown-menu\">"         
                +  "<li class=\"action_li\" data-func=\"pieIconByObservations\"><a href=\"#\">תצפיות לזן</a></li>"
                +  "<li class=\"action_li\" data-func=\"pieIconByTax\"><a href=\"#\">התפלגות הזנים</a></li>"
            +  "</ul>"
      +  "</li>"
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>עמודות</a>"
            +  "<ul class=\"dropdown-menu\">"                
                +  "<li class=\"action_li\" data-func=\"barTaxonsByObservations\"><a href=\"#\">התפלגות תצפיות לפי זן עבור מתצפת</a></li>"
            +  "</ul>"
      +  "</li>" 
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"timeByObservations\"><a href=\"#\">התפלגות התצפיות על פני זמן</a></li>"  
                +  "<li class=\"action_li\" data-func=\"timeByTaxonObservations\"><a href=\"#\">התפלגות התצפיות לזן על פני זמן</a></li>"  
            +  "</ul>"
      +  "</li>"       
        );

        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     });  
    }
    
    function _paintThirdMenuGraph(){
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>פאי</a>"
            +  "<ul class=\"dropdown-menu\">"         
                +  "<li class=\"action_li\" data-func=\"donutTaxonDrillUsersObs\"><a href=\"#\">אחוז תצפיות למתצפת למין נבחר</a></li>"
            +  "</ul>"
      +  "</li>"
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>עמודות</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"barTaxonsByObservations\"><a href=\"#\">תצפיות לפי מתצפת למין נבחר</a></li>"
            +  "</ul>"
      +  "</li>" 
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"timeByObservations\"><a href=\"#\">התפלגות התצפיות על פני זמן</a></li>"  
                +  "<li class=\"action_li\" data-func=\"timeByTaxonObservations\"><a href=\"#\">התפלגות התצפיות של מתצפתים לפי מין נבחר</a></li>"               
            +  "</ul>"
      +  "</li>"       
        );

        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     });  
    }
    
    function _paintFourthMenuGraph(){
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>פאי</a>"
            +  "<ul class=\"dropdown-menu\">"         
                +  "<li class=\"action_li\" data-func=\"donutTaxonDrillUsersObs\"><a href=\"#\">אחוז תצפיות למתצפת למין נבחר</a></li>"

            +  "</ul>"
      +  "</li>"
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>עמודות</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"barTaxonsByObservations\"><a href=\"#\">התפלגות תצפיות למין נבחר</a></li>"
            +  "</ul>"
      +  "</li>" 
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"                   
                +  "<li class=\"action_li\" data-func=\"timeByTaxonObservations\"><a href=\"#\">התפלגות התצפיות לזן על פני זמן</a></li>"
            +  "</ul>"
      +  "</li>"       
        );

        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     });  
    }
    
    function _paintFifthMenuGraph(){
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"                   
                +  "<li class=\"action_li\" data-func=\"timeByObservations\"><a href=\"#\">התפלגות התצפיות למתצפת עבור מין נבחר</a></li>"                
            +  "</ul>"
      +  "</li>"       
        );

        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     });  
    }
    
    function _paintSixthMenuGraph(){
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>פאי</a>"
            +  "<ul class=\"dropdown-menu\">"         
                +  "<li class=\"action_li\" data-func=\"pieTaxByUser\"><a href=\"#\">אחוז תצפיות למתצפת למין נבחר</a></li>"

            +  "</ul>"
      +  "</li>"
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>עמודות</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"barTaxonsByObservations\"><a href=\"#\">התפלגות תצפיות למין נבחר למתצפת</a></li>"
            +  "</ul>"
      +  "</li>" 
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"                   
                +  "<li class=\"action_li\" data-func=\"timeByTaxonObservations\"><a href=\"#\">התפלגות התצפיות לזן על פני זמן</a></li>"
            +  "</ul>"
      +  "</li>"       
        );

        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     });  
    }
    
    function _paintEightMenuGraph(){
        $("#action_menu").empty(); 
        $("#action_menu").append( 
         "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>פאי</a>"
            +  "<ul class=\"dropdown-menu\">"         
                  +  "<li class=\"action_li\" data-func=\"pieIconByObservations\"><a href=\"#\">תצפיות לזן</a></li>"       
            +  "</ul>"
      +  "</li>"
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>עמודות</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"barTaxonsByObservations\"><a href=\"#\">התפלגות התצפיות לפי זן</a></li>"
            +  "</ul>"
      +  "</li>"  
      +  "<li class=\"dropdown-submenu\"><a class=\"sub\" href=\"#\"><span class=\"left-caret\"></span>זמן</a>"
            +  "<ul class=\"dropdown-menu\">"          
                +  "<li class=\"action_li\" data-func=\"timeByObservations\"><a href=\"#\">התפלגות התצפיות על פני זמן</a></li>"     
            +  "</ul>"
      +  "</li>"         
        );

        $('.dropdown-submenu a.sub').on("click", function(e){
        $(this).next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
     });  
    }
    
    
          
    $("#action_menu").on("click", ".action_li", function(event){  
        window[$(this).attr("data-func")]();
    });
         
      //-------------------- function that check if date between two dates for the seasions -------------------------------------  
   
    function graphSelector() {
        switch(graphFlag) {
            case 1:
                pieIconByTax();
                break;
            case 2:
                pieTaxByUser();
                break;
            case 3:
                donutTaxonDrillUsersObs();
                break;
            case 4:
                pieTaxonByTax();
                break;
            case 5:
                pieTaxonsByUsers();
                break;
            case 6:
                pieIconByObservations();
                break;
            case 7:
                barTaxonsByObservations();
                break;
            case 8:
                timeByObservations();
                break;
            case 9:
                timeByTaxonObservations();
                break;
            case 10:
                seassionObservations();
                break;      
         } 
       
         $('#main-panel-container').removeClass('loading');
    } 
    
  //-------------------------------------------- Create the data for the charts ------------------------------------------------
      
      // This function generate and build pie chart that present the distribution of the iconic taxons in a project
      pieIconByTax = function() {

        var projectName = getName($("#project-list .selected .username"));    
        graphFlag = 1;                
        var data = Array();
        
        var sum = 0;
        for (var iconic in groups)
           sum+= groups[iconic]['iconicTax'];
          
        for (var iconic in groups)
            data.push(new Array(converateIconName(iconic), (groups[iconic]['iconicTax']/sum)*100));
        var userName = getName($("#user-list .selected .username"));
        if (userName!=="")
            _paintPieChart(data, 'התפלגות הזנים','מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','אחוז המינים בזן זה');
        else
             _paintPieChart(data, 'התפלגות הזנים' ,'פרויקט: ' +  projectName,'graph-container','אחוז המינים בזן זה');

    };

      // This function generate and build pie chart that present the distribution of the iconic taxons in a project
      pieTaxByUser = function() {
        graphFlag = 2;
        var projectName = getName($("#project-list .selected .username"));    
        
        var data = Array();  
        
        var sum = 0;
        for (var tax in selectedTaxonsUsersObs)
           sum+= selectedTaxonsUsersObs[tax]['obsCount'];  
          
        for (var tax in selectedTaxonsUsersObs){   
//             var taxTemp = selectedTaxonsUsersObs[tax];  
              data.push(new Array(selectedTaxonsUsersObs[tax]['name'], (selectedTaxonsUsersObs[tax]['obsCount']/sum)*100));
         }    
         var userName = getName($("#user-list .selected .username"));

         _paintPieChart(data, 'אחוז התצפיות למתצפת עבור מין נבחר', 'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','אחוז התצפיות למתצפת');     
    };
    
      // This function generate and build donut chart that present the precentege of users observations in every selected taxon
      donutTaxonDrillUsersObs = function() {
        graphFlag = 3;
        var projectName = getName($("#project-list .selected .username"));       
        var colors = Highcharts.getOptions().colors;
        var taxons = [];
        var data = [];
        var totalTaxonObs = 0;
        var colorCount = 0 ;
        for (var tax in selectedTaxonsUsersObs){   
             var taxTemp = selectedTaxonsUsersObs[tax];  
             taxons.push(taxTemp.name);
             var subCategories = [];
             var subData = [];
             for (var user in taxTemp.Users)
             {
                subCategories.push(taxTemp.Users[user].login);
                subData.push(taxTemp.Users[user].userObs);
             }

             var obj = {
                 y: taxTemp.taxonObs,
                 color: colors[colorCount],
                    drilldown: {
                        name: taxTemp.name,
                        categories: subCategories,
                        data: subData,
                        color: colors[colorCount]
                    }
              }; 
              totalTaxonObs += taxTemp.taxonObs;
              data.push(obj);
              colorCount++;
        }                 

        var taxonsData = [];
        var usersData = [];
        var dataLen = data.length;
        var drillDataLen;
        var brightness;

        // Build the data arrays
        for (var i = 0; i < dataLen; i += 1) {

            taxonsData.push({              
                name: taxons[i],               
                y: (data[i].y/totalTaxonObs)*100,
                color: data[i].color
            });

            // add version data
            drillDataLen = data[i].drilldown.data.length;
            for (var j = 0; j < drillDataLen; j += 1) {
                brightness = 0.2 - (j / drillDataLen) / 5;
                usersData.push({
                    name: data[i].drilldown.categories[j],
                    y: (data[i].drilldown.data[j]/totalTaxonObs)*100,
                    color: Highcharts.Color(data[i].color).brighten(brightness).get()
                });
            }
        }  

         if (menuFlag === 4)
         {
              _paintDonutChart(usersData,taxonsData, 'אחוז התצפיות לכל מתצפת בין המינים שנבחרו', 'פרויקט: ' +  projectName,'graph-container');
          }else if (menuFlag === 3)
         {
              var taxonName = getName($("#taxa-list .selected .username"));            
              _paintDonutChart(usersData,taxonsData, 'אחוז התצפיות לכל מתצפת עבור מין נבחר', 'מין: ' + ' ' + taxonName + ', פרויקט: ' + projectName,'graph-container');   
          }
    };   

      // This function generate and build pie chart that present the distribution of the iconic taxons in a project
      pieTaxonByTax = function() {
        graphFlag = 4;
        var projectName = getName($("#project-list .selected .username"));    
                
        var data = Array();
        for (var iconic in groups)
            data.push(new Array(converateIconName(iconic), groups[iconic]['iconicTax']));
        var userName = getName($("#user-list .selected .username"));
        if (userName!=="")
            _paintPieChart(data, 'התפלגות הזנים','מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','אחוז המינים בזן זה');
        else
             _paintPieChart(data, 'התפלגות הזנים' ,'פרויקט: ' +  projectName,'graph-container','אחוז המינים בזן זה');
      
    };
        
     //function generate and build pie chart that present the distribution of the amount of users per iconic taxon in a project 
     pieTaxonsByUsers = function () {
        graphFlag = 5;
        var projectName = getName($("#project-list .selected .username"));
        var data = Array();
        for (var iconic in groups) {
            var sum = 0;
            groups[iconic].forEach(function (arrayItem) {
                sum += $(arrayItem.users).length;
            });
            data.push(new Array(converateIconName(iconic), sum));
        }
        _paintPieChart(data, 'התפלגות התצפיות לכל זן' , 'פרויקט: ' +  projectName, 'graph-container', 'אחוז המתצפתים לזן בפרויקט');
    };

     //This function generate and build pie chart that present the distribution of the amount of observations per iconic taxon in a project 
     pieIconByObservations =  function() {
         
        graphFlag = 6;
        var projectName = getName($("#project-list .selected .username"));
        var data = Array();
        if (menuFlag === 1 || menuFlag === 2){
            
            var sum = 0;
            for (var iconic in groups)
                sum+= groups[iconic]['iconicObs'];
            
            for (var iconic in groups)
                data.push(new Array(converateIconName(iconic), (groups[iconic]['iconicObs']/sum)*100));

            var userName = getName($("#user-list .selected .username"));
            if (userName!=="")
                _paintPieChart(data, 'התפלגות התצפיות בין הזנים השונים','מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','אחוז התצפיות לזן זה');
            else
                 _paintPieChart(data, 'התפלגות התצפיות בין הזנים השונים' ,'פרויקט: ' +  projectName,'graph-container','אחוז התצפיות לזן זה');
         }else if (menuFlag === 7)
         {
             var sum = 0;
             for (var tax in selectedProjectTaxonObs)
                sum+= selectedProjectTaxonObs[tax]['taxObs'];
            
             for (var tax in selectedProjectTaxonObs)
                data.push(new Array(selectedProjectTaxonObs[tax].name, (selectedProjectTaxonObs[tax]['taxObs']/sum)*100));
            
              _paintPieChart(data, 'התפלגות התצפיות בין המינים השונים עבור זן - ' + converateIconName(selectedPill.attr('id')),'פרויקט: ' +  projectName,'graph-container','אחוז התצפיות למין');
         }else if (menuFlag === 8)
         {
             var sum = 0;
             for (var tax in selectedUsersTaxonObs)
                sum+= selectedUsersTaxonObs[tax]['taxObs'];
             for (var tax in selectedUsersTaxonObs)
                data.push(new Array(selectedUsersTaxonObs[tax].name, (selectedUsersTaxonObs[tax]['taxObs']/sum)*100));
            
             var userName = getName($("#user-list .selected .username"));
             _paintPieChart(data, 'התפלגות התצפיות בין המינים השונים עבור זן - ' + converateIconName(selectedPill.attr('id')),'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','אחוז התצפיות למין');
         }
         
         
         
         
   };
        
     //This function generate and build bar chart that present the distribution of the amount of observations per iconic taxon in a project including drilldown
     barTaxonsByObservations = function() {
        
           graphFlag = 7;  
            var projectName = getName($("#project-list .selected .username"));
            var data = Array();
            var series = Array();
            
            if (menuFlag === 1 || menuFlag === 2){
              
                for (var iconic in groups){

                    var sData =Array();
                    groups[iconic]['Taxons'].forEach(function (arrayItem){

                        sData.push(new Array(arrayItem.name,arrayItem.taxObs));
                    });    
                    // build the object for the main taxa
                    var d ={name: converateIconName(iconic),
                            y: groups[iconic]['iconicObs'],
                            drilldown: iconic  
                    };
                    //build the object of the explose taxa for the drilldown
                    var s = {name: converateIconName(iconic),
                             id: iconic,
                             data: sData
                            };

                    data.push(d);
                    series.push(s);

                }
                 var userName = getName($("#user-list .selected .username"));

                  if (userName!=="")
                     _paintBarChart(data, 'התפלגות התצפיות לכל זן' ,'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','תצפיות לזן',series,'מספר התצפיות','תצפיות לזן','תצפיות לזן','התפלגות התצפיות לפי מינים עבור זן - ');
                 else
                     _paintBarChart(data, 'התפלגות התצפיות לכל זן' , 'פרויקט: ' +  projectName,'graph-container','תצפיות לזן',series,'מספר התצפיות','תצפיות לזן','תצפיות לזן','התפלגות התצפיות לפי מינים עבור זן - ');                
              }  
              else if (menuFlag === 4) {
                  
                 var taxTemp;
                 for (var tax in selectedTaxonsUsersObs) {
                    taxTemp = selectedTaxonsUsersObs[tax];
                    var sData =Array(); 
                    for (var user in taxTemp.Users){
                        
                            sData.push(new Array(taxTemp.Users[user].login,parseInt(taxTemp.Users[user].userObs)));
                         }
                         
                     var d ={name: selectedTaxonsUsersObs[tax].name,
                            y: selectedTaxonsUsersObs[tax].taxonObs,
                            level: 0,
                            drilldown: selectedTaxonsUsersObs[tax].name  
                    };
                     
                    //build the object of the explose taxa for the drilldown
                    var s = {name: selectedTaxonsUsersObs[tax].name,
                             id: selectedTaxonsUsersObs[tax].name,
                             level: 1,
                             data: sData
                            };

                    data.push(d);
                    series.push(s);

                    }   
                      _paintBarChart(data, 'התפלגות התצפיות לכל זן' , 'פרויקט: ' +  projectName,'graph-container','תצפיות לזן',series,'מספר התצפיות','תצפיות לזן','תצפיות למשתמש','התפלגות התצפיות לפי משתמשים עבור זן - ');
                } 
                else if (menuFlag === 3) {
                    
                    var taxTemp;
                    for (var tax in selectedTaxonsUsersObs) 
                       taxTemp = selectedTaxonsUsersObs[tax];

                    for (var user in taxTemp.Users){


                       var d ={name: taxTemp.Users[user].login,
                                  y: parseInt(taxTemp.Users[user].userObs),
                                  level: 0
                          };
                       data.push(d);  
                   }    

                   var taxonName = getName($("#taxa-list .selected .username"));
                    _paintBarChart(data, 'התפלגות התצפיות לפי מתצפתים' ,'מין: ' + ' ' + taxonName + ', פרויקט: ' + projectName,'graph-container','תצפיות למתצפת',null,'מספר התצפיות','תצפיות למתצפת',null,null);           
                } 
                else if (menuFlag === 6) {
                    
                    var taxTemp;
                    for (var tax in selectedTaxonsUsersObs){ 
                       taxTemp = selectedTaxonsUsersObs[tax];
                        var d ={name: taxTemp.name,
                                  y: parseInt(taxTemp.obsCount),
                                  level: 0
                          };
                       data.push(d);  
                       
                   }
           
                    var userName = getName($("#user-list .selected .username"));
                    _paintBarChart(data, 'התפלגות התצפיות לפי מין נבחר למתצפת', 'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','תצפיות למתצפת',null,'מספר התצפיות','תצפיות למין',null,null);           
                } 
                else if (menuFlag === 7) {
                   
                    var taxTemp;
                    for (var tax in selectedProjectTaxonObs){ 
                       taxTemp = selectedProjectTaxonObs[tax];
                        var d ={name: taxTemp.name,
                                  y: parseInt(taxTemp.taxObs),
                                  level: 0
                          };
                       data.push(d);  
                       
                   }                         
                    _paintBarChart(data, 'התפלגות התצפיות עבור מין - ' + converateIconName(selectedPill.attr('id')), 'פרויקט: ' +  projectName,'graph-container','תצפיות לזן',null,'מספר התצפיות','תצפיות למין',null,null);
                } 
                else if (menuFlag === 8) {
               
                    var taxTemp;
                    for (var tax in selectedUsersTaxonObs){ 
                       taxTemp = selectedUsersTaxonObs[tax];
                        var d ={name: taxTemp.name,
                                  y: parseInt(taxTemp.taxObs),
                                  level: 0
                          };
                       data.push(d);  
                       
                   }  
                    var userName = getName($("#user-list .selected .username"));
                    _paintBarChart(data, 'התפלגות התצפיות עבור מין - ' + converateIconName(selectedPill.attr('id')), 'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container','תצפיות לזן',null,'מספר התצפיות','תצפיות למין',null,null);
                }                                     
        };

    //This function generate and build time chart that present the distribution of the amount of observations per date in a project including drilldown
     timeByObservations = function() {
            graphFlag = 8;
            var projectName = getName($("#project-list .selected .username"));
            var data = Array();
            
             if (menuFlag === 2)
             {
                selectedUsersObs.forEach(function (arrayItem){
                    data.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });  
                var userName = getName($("#user-list .selected .username"));
                _paintTimeChart(data, 'התפלגות התצפיות' ,'מתצפת: ' + userName + ', פרויקט: ' + projectName,'graph-container','תצפיות לזן',null,'מספר התצפיות');
            } else if (menuFlag === 1) 
            {
         
                selectedProjectObs.forEach(function (arrayItem){
                     data.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });
                _paintTimeChart(data,'התפלגות התצפיות','פרויקט: ' +  projectName,'graph-container','תצפיות לזן',null,'מספר התצפיות');
            } else if (menuFlag === 3) 
            {
                var taxTemp;
                for (var tax in selectedTaxonsUsersObs) 
                    taxTemp = selectedTaxonsUsersObs[tax];
              
                taxTemp.ObsByDateAndTaxon.forEach(function (arrayItem){
                     data.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });
                var taxonName = getName($("#taxa-list .selected .username"));
                _paintTimeChart(data,'התפלגות התצפיות','מין: ' + ' ' + taxonName + ', פרויקט: ' + projectName,'graph-container','תצפיות לזן',null,'מספר התצפיות');
            } else if (menuFlag === 5) 
            {
                var taxTemp;
                for (var tax in selectedTaxonsUsersObs) 
                    taxTemp = selectedTaxonsUsersObs[tax];
              
                taxTemp.ObsByDateAndTaxon.forEach(function (arrayItem){
                     data.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });
                var userName = getName($("#user-list .selected .username"));
                var taxonName = getName($("#taxa-list .selected .username"));
                _paintTimeChart(data,'התפלגות התצפיות','מתצפת: ' + userName + ', מין: ' + taxonName + ', פרויקט: ' + projectName,'graph-container','תצפיות לזן',null,'מספר התצפיות');
            } else if (menuFlag === 7) 
            {            
               selectedProjectObs.forEach(function (arrayItem){
                     data.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                 });     
              
                _paintTimeChart(data,'התפלגות התצפיות עבור מין - ' + converateIconName(selectedPill.attr('id')),'פרויקט: ' + projectName,'graph-container','תצפיות למין',null,'מספר התצפיות');
            }  else if (menuFlag === 8) 
            {            
               selectedUsersObs.forEach(function (arrayItem){
                     data.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                 });     
                var userName = getName($("#user-list .selected .username"));
                _paintTimeChart(data,'התפלגות התצפיות עבור מין - ' + converateIconName(selectedPill.attr('id')),'מתצפת: ' + userName + ', פרויקט: ' + projectName,'graph-container','תצפיות למין',null,'מספר התצפיות');
            }        
            
            
        };

     timeByTaxonObservations = function() {
            
        graphFlag = 9;
         var projectName = getName($("#project-list .selected .username"));
            var series = Array();
            
            if (menuFlag === 2)
             {
               for (var iconic in selectedUsersTaxonObs){           
                 var sData =Array();
                selectedUsersTaxonObs[iconic].forEach(function (arrayItem){

                    sData.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });    
                // build the object for the main taxa
                var d ={name: converateIconName(iconic),
                        data: sData  
                };
                series.push(d);
               
              }    
              var userName = getName($("#user-list .selected .username"));
              _paintLineTimeChart(null, 'התפלגות התצפיות לפי זן' ,'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container',null,series,'מספר התצפיות');     
              return false;
              
            } else if (menuFlag === 1){
              for (var iconic in selectedProjectTaxonObs){
           
                var sData =Array();
                selectedProjectTaxonObs[iconic].forEach(function (arrayItem){

                    sData.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });    
                // build the object for the main taxa
                var d ={name: converateIconName(iconic),
                        data: sData  
                };
                series.push(d);              
               } 
            }else if (menuFlag === 4){

                var taxTemp;
                for (var tax in selectedTaxonsUsersObs) {
                    taxTemp = selectedTaxonsUsersObs[tax];
                    var sData =Array(); 
                    taxTemp.ObsByDateAndTaxon.forEach(function (arrayItem){          
                        sData.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });    
                // build the object for the main taxa
                var d ={name: taxTemp.name,
                        data: sData  
                };
                series.push(d);              
               }               
            }else if (menuFlag === 3){
              
                var taxTemp;
                for (var tax in selectedTaxonsUsersObs) 
                    taxTemp = selectedTaxonsUsersObs[tax];
                for (var user in taxTemp.Users){
                 
                     var sData =Array(); 
                     taxTemp.Users[user].ObsByDate.forEach(function (obsItem){    
                        sData.push(new Array(Date.parse(obsItem.date),parseInt(obsItem.obsCount)));
                     });
                   
                    // build the object for the main taxa
                    var d ={name: taxTemp.Users[user].login,
                            data: sData  
                     };
                    series.push(d); 
                        
                }
            } else if ( menuFlag === 6){

                var taxTemp;
                for (var tax in selectedTaxonsUsersObs) {
                    taxTemp = selectedTaxonsUsersObs[tax];
                    var sData =Array(); 
                    taxTemp.ObsByDateAndTaxon.forEach(function (arrayItem){          
                        sData.push(new Array(Date.parse(arrayItem.date),parseInt(arrayItem.obsCount)));
                });    
                // build the object for the main taxa
                var d ={name: taxTemp.name,
                        data: sData  
                };
                series.push(d);              
               } 
               
                var userName = getName($("#user-list .selected .username"));
                _paintLineTimeChart(null,'התפלגות התצפיות למתצפת עבור מין נבחר' ,'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName,'graph-container',null,series,'מספר התצפיות');         
                return false;
            }
            
             _paintLineTimeChart(null,'התפלגות התצפיות לפי מין נבחר' ,'פרויקט: ' +  projectName,'graph-container',null,series,'מספר התצפיות');         
             
        };    
    
     seassionObservations = function() {
           graphFlag = 10;
           var subTitle="";
           var projectName = getName($("#project-list .selected .username"));

           if (menuFlag === 1)
           {    
               subTitle = 'פרויקט: ' +  projectName; 
               
           } else if (menuFlag === 2) 
           {         
               var userName = getName($("#user-list .selected .username"));
               subTitle = 'מתצפת: ' + ' ' + userName + ', פרויקט: ' + projectName;  
               
           } else if (menuFlag === 7) 
           {         
              subTitle = 'זן: ' + ' ' + converateIconName(selectedPill.attr('id')) + ', פרויקט: ' + projectName;       
           } else if (menuFlag === 3) 
           {         
              var taxonName = getName($("#taxa-list .selected .username"));  
              subTitle = 'מין: ' + ' ' + taxonName + ', פרויקט: ' + projectName;        
           }  else if (menuFlag === 4) 
           {         
              subTitle = 'מין: ' + ' ' + 'משולב' + ', פרויקט: ' + projectName;        
           } else if ( menuFlag === 5) 
           {                  
              var taxonName = getName($("#taxa-list .selected .username"));  
              var userName = getName($("#user-list .selected .username"));
              subTitle ='מתצפת: ' + ' ' + userName + ', מין: ' + ' ' + taxonName + ', פרויקט: ' + projectName;                                        
           } else if ( menuFlag === 6) 
           {                               
              var userName = getName($("#user-list .selected .username"));
              subTitle ='מתצפת: ' + ' ' + userName + ', מין: ' + ' ' + 'משולב' + ', פרויקט: ' + projectName;                                        
           } else if ( menuFlag === 8) 
           {                   
              var userName = getName($("#user-list .selected .username"));
              subTitle ='מתצפת: ' + ' ' + userName + ', זן: ' + ' ' + converateIconName(selectedPill.attr('id')) + ', פרויקט: ' + projectName;                                        
           }
           var seassionSum = 0 ;
            var series = Array();
            for (var year in selectedSeassionsObs[selectedSeassion.attr('id')]){   
               if (selectedSeassionsObs[selectedSeassion.attr('id')][year]['grouped'].length >= 1)
               {
                   seassionSum += selectedSeassionsObs[selectedSeassion.attr('id')][year]['observation'].length;
                   if (selectedSeassion.attr('id')==='winter')
                   {
                        var d ={name: converateSeassionName(selectedSeassion.attr('id')) + ' ' + (+year-1) + '-' + year,
                        data: selectedSeassionsObs[selectedSeassion.attr('id')][year]['grouped'] 
                        }; 
                   } else{
                    var d ={name: converateSeassionName(selectedSeassion.attr('id')) + ' ' + year,
                            data: selectedSeassionsObs[selectedSeassion.attr('id')][year]['grouped']  
                            };
                    }              
                    series.push(d);
                }
           }

           _repaint_sums(null,null,seassionSum);
           _paintSeasionTimeChart(null,'השוואת התפלגות רב שנתית של התצפיות בתקופת ה' + converateSeassionName(selectedSeassion.attr('id')),subTitle,'graph-container',null,series,'מספר התצפיות');
        };
          
     function converateSeassionName(name){
         switch(name) {             
            case "winter":
                return 'חורף';         
            case "summer":
                return 'קיץ';
            case "spring":
                return 'אביב';
            case "autumn":
                return 'סתיו';           
          }
     }   
 
  //-------------------- function that get the name of selected item in the the html and return it ---------------------------
    
     function getName(source) {
             var   newSource = source   
                    .clone()    //clone the element
                    .children() //select all the children
                    .remove()   //remove all the children
                    .end()  //again go back to selected element
                    .text(); //.replace(/ /g,'');
      return newSource;
    }
  
  //-------------------------------------------- Create and render the charts ------------------------------------------------
    function _paintPieChart(data,title,subTitle,place,series){
    graph = new Highcharts.Chart({
        chart: {
            type: 'pie',
            renderTo: place,
            reflow: true, 
            events: {
                load: function(){graph=this;}
                },
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
                   
        },
        title: {
            text: title
        },
        subtitle: {
                text: subTitle
            },
        tooltip: {
            valueSuffix: '%',
            headerFormat: '<b style="color:{series.color}; float:right;">{series.name}</b><br/>',
            pointFormat: '<b>{point.name} : {point.y} </b>',  
            valueDecimals: 1,
            useHTML: true
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
//                distance: -35,
                dataLabels: {
                    enabled: true,
                    useHTML: true,
                    distance: 15,
                    formatter: function () {
                    // display only if larger than 1
                    return this.y > 2.0 ? '<b>' + this.point.name + ':</b> ' + Highcharts.numberFormat(this.y,'1') + '%' : null;
                }
                }
            }
        },
        series: [{
            type: 'pie',
            name: series,
            data: data
        }]
        
    });
  }
  
    function _paintDonutChart(usersData,taxonsData,title,subTitle,place){
          
        graph = new Highcharts.Chart({
        chart: {
            type: 'pie',
            renderTo: place,
            reflow: true
        },
        title: {
            text: title
        },
        subtitle: {
                text: subTitle
            },
        tooltip: {
            valueSuffix: '%',
            headerFormat: '<b style="color:{series.color}; float:right;">{series.name}</b><br/>',
            pointFormat: '<b>{point.name} : {point.y} </b>',  
            valueDecimals: 1,
            useHTML: true
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                shadow: true,
                center: ['50%', '50%']
            }
        },
         series: [{
            name: 'זן:',
            data: taxonsData,
            size: '60%',
            dataLabels: {

                useHTML: true,
                inside: true,
                formatter: function () {
                    return this.y > 10.0 ? this.point.name : null;
                },
                color: '#ffffff',
                distance: -35,
                valueDecimals: 1
            }
        }, {
            name: 'מתצפת:',
            data: usersData,
            size: '80%',
            innerSize: '60%',
            dataLabels: {
              

              useHTML: true,
              valueDecimals: 1,
          
                formatter: function () {
                    // display only if larger than 1
                    return this.y > 4.0 ? '<b>' + this.point.name + ':</b> ' + Highcharts.numberFormat(this.y,'1') + '%' : null;
                }
            }
        }]
        
    });
  }

    Highcharts.setOptions({lang: {noData: "אין מידע לתצוגה"}, noData: {style: {
                                            fontWeight: 'bold',
                                            fontSize: '20px',
                                            color: 'red',                        
                                            margin: 0,
                                            padding: 0,
                                            direction: 'ltr'}}});
                                
    function _paintBarChart(data,title,subTitle,place,series_name,series,yTitle,level0,level1,level1Title){    
       
    Highcharts.setOptions({lang: {drillUpText: '< ' + 'חזרה לגרף: {series.name}'
                                        }
                                    });
        
        graph = new Highcharts.Chart({
        chart: {
          type: 'column',
          renderTo: place,
           reflow: true,
          events: {
//                redraw: function () {
//                
//             
//                console.log(graph.series[0].data.length);
//                    if ( graph.series[0].data.length >= 40 && graph.series[0].data.length >= 80)
//                         graph.xAxis[0].update ({labels: { step : 2 }});
//                    else if ( graph.series[0].data.length >= 81 && graph.series[0].data.length >= 150)
//                         graph.xAxis[0].update ({labels: { step : 4 }}); 
//                     else if (graph.series[0].data.length > 151 && graph.series[0].data.length >= 300)
//                         graph.xAxis[0].update ({labels: { step : 6 }});
//                     else if (graph.series[0].data.length > 301)
//                         graph.xAxis[0].update ({labels: { step : 8 }});
//                },
                
                    
                  drilldown: function(e) {
                   console.log(e);
                    graph.setTitle({ text: level1Title + ' <b>' + e.point.name + '</b>'});
                    
               
                    if (  e.seriesOptions.data.length <= 40)
                    {                     
                         graph.xAxis[0].update ({labels: { step : 1 ,style:{ cursor: 'default',
                                                                                    color: 'black',
                                                                                    fontWeight: 'normal',
                                                                                    textDecoration: 'none'  }}});
                     } else  if ( e.seriesOptions.data.length >= 41 && e.seriesOptions.data.length <= 80)
                    {                     
                         graph.xAxis[0].update ({labels: { step : 2 ,style:{ cursor: 'default',
                                                                                    color: 'black',
                                                                                    fontWeight: 'normal',
                                                                                    textDecoration: 'none'  }}});
                     }                    
                    else if ( e.seriesOptions.data.length >= 81 && e.seriesOptions.data.length <= 150)
                         graph.xAxis[0].update ({labels: { step : 4 ,style:{ cursor: 'default',
                                                                                    color: 'black',
                                                                                    fontWeight: 'normal',
                                                                                    textDecoration: 'none'  }}});
                     else if (e.seriesOptions.data.length > 151 && e.seriesOptions.data.length <= 300)
                         graph.xAxis[0].update ({labels: { step : 15 ,style:{ cursor: 'default',
                                                                                    color: 'black',
                                                                                    fontWeight: 'normal',
                                                                                    textDecoration: 'none'  }}});
                     else if (e.seriesOptions.data.length > 301)
                         graph.xAxis[0].update ({labels: { step : 20 ,style:{ cursor: 'default',
                                                                                    color: 'black',
                                                                                    fontWeight: 'normal',
                                                                                    textDecoration: 'none'  }}});
                },
                drillup: function(e) {
                    graph.setTitle({ text: title });
                    graph.xAxis[0].update ({labels: { step : 1 ,style:{ cursor: 'pointer',
                                                                                    color: 'black',
                                                                                    fontWeight: 'bold',
                                                                                    textDecoration: 'underline'  }}});                                         
                }
            }
        },
        title: {
          text: title
        },
        subtitle: {
          text:  subTitle
        },
        xAxis: {
          type: 'category',             
          labels: {             
                x: 0,
                y: 35,
                align:'center',
//                formatter: function() { return this.value;},
                enabled: true
//                style: {
//                    color: 'black',
//                    textDecoration: 'none',
//                    cursor: 'default',
//                    fontWeight: 'normal'   
//                    
//                } formatter: function () {
//                    return '<a href="' + categoryLinks[this.value] + '">' +
//                        this.value + '</a>';
//                }
          }
        },
        yAxis: {
          title: {
            text: yTitle
          }
        },
        legend: {
          enabled: false
        },
        plotOptions: {
          series: {
            borderWidth: 0,
            dataLabels: {
              enabled: true,
              format: '{point.y:.0f}',
              useHTML: true,
              inside: true
            }
          }         
        },

        tooltip: {
             formatter: function() {
                 if(this.series.options.level === 1)  
                    return 'סך של: ' + '<b> '+ this.y + '</b> ' + level1  +  ' - <b>'+ this.point.name +'</b>'  ;        
                 else
                    return 'סך של: ' + '<b> '+ this.y + '</b> ' + level0  +  ' - <b>'+ this.point.name +'</b>'  ; 
            },
            useHTML: true
        },

        series: [{
          name: series_name,
          colorByPoint: true,
          data: data
        }],
        drilldown: {
            activeAxisLabelStyle: {
                cursor: 'pointer',
                color: '#0d233a',
                fontWeight: 'bold',
                textDecoration: 'underline'          
            },
            drillUpButton: {
                relativeTo: 'plotBox',
                position: {
                    y: 0,
                    x: 0
                },
                theme: {
                    style: {
                        margin: 0,
                        padding: 0,
                        direction: 'ltr'
                    }                  
                }
            },
          
             series: series 

        }
      });
    }
    
    Highcharts.setOptions({lang: {noData: "אין מידע לתצוגה"}, noData: {style: {
                                            fontWeight: 'bold',
                                            fontSize: '20px',
                                            color: 'red',                        
                                            margin: 0,
                                            padding: 0,
                                            direction: 'ltr'}}});
    
    function _paintTimeChart(data,title,subTitle,place,series_name,series,yTitle){    
       
        Highcharts.setOptions({
            global: {
                useUTC: false
            },
            lang: {resetZoom: 'איפוס תקריב'
               ,shortMonths: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',  'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'] 
               ,months: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',  'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'],
               weekdays: ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת']
                                        }                 
        });
   
        graph = new Highcharts.Chart({    
            chart: {
                zoomType: 'x',
                renderTo: place,
                reflow: true
            },
            title: {
                text: title
            },
            subtitle: {
                text: subTitle
//                text: 'הקלק ומשוך על מנת להתמקד בטווח מסויים'
            },
            credits : {
            enabled: false
            },
            xAxis: {      
                type: 'datetime'
            },
            yAxis: {
                title: {
                    text: yTitle
                },
                min:0
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, Highcharts.getOptions().colors[2]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[2]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    dataLabels: {
                        useHTML:true
                    },
                    threshold: 0                 
                }
            },

        tooltip: {
            xDateFormat: '%A, %b %e, %Y',
            useHTML: true
        },    
        drilldown: {
            drillUpButton: {
                relativeTo: 'plotBox',
                position: {
                    y: 0,
                    x: 0
                },
                theme: {
                    style: {
                        margin: 0,
                        padding: 0,
                        direction: 'ltr'
                    }                  
                }
            }},
            series: [{
                type: 'area',
                name: yTitle,
                data: data
            }]
        });

    }
    
    function _paintLineTimeChart(data,title,subTitle,place,series_name,series,yTitle){    
       
        Highcharts.setOptions({
            global: {
                useUTC: false
            },
            lang: {resetZoom: 'איפוס תקריב'
               ,shortMonths: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',  'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'] 
               ,months: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',  'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'],
               weekdays: ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת']
                                        }                 
        });
   
        graph = new Highcharts.Chart({    
            chart: {
                zoomType: 'x',
                renderTo: place
            },
            title: {
                text: title
            
            },
            subtitle: {
              text: subTitle  
             //   text: 'הקלק ומשוך על מנת להתמקד בטווח מסויים'
            },
            credits : {
            enabled: false
            },
            xAxis: {      
                type: 'datetime'
            },
            yAxis: {
                title: {
                    text: yTitle
                },
           
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
                }]
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 1,
                useHTML: true
            },            plotOptions: {
                area: {

                    marker: {
                        radius: 2,
                        enabled: true
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    dataLabels: {
                        useHTML:true
                    },
                    threshold: 0                 
                }
            },
            
            
         tooltip: {
            xDateFormat: '%A, %b %e, %Y',
            useHTML: true
        },drilldown: {
            drillUpButton: {
                relativeTo: 'plotBox',
                position: {
                    y: 0,
                    x: 0
                },
                theme: {
                    style: {
                        margin: 0,
                        padding: 0,
                        direction: 'ltr'
                    }                  
                }
            }},    
            series: series
        });
    
   }   
   
    function _paintSeasionTimeChart(data,title,subTitle,place,series_name,series,yTitle){    
       
        Highcharts.setOptions({
            global: {
                useUTC: true
            },
            lang: {resetZoom: 'איפוס תקריב'
               ,shortMonths: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',  'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'] 
               ,months: ['ינואר', 'פברואר', 'מרץ', 'אפריל', 'מאי', 'יוני',  'יולי', 'אוגוסט', 'ספטמבר', 'אוקטובר', 'נובמבר', 'דצמבר'],
               weekdays: ['ראשון', 'שני', 'שלישי', 'רביעי', 'חמישי', 'שישי', 'שבת']
                                        }                 
        });
   
        graph = new Highcharts.Chart({    
            chart: {
                type: 'spline',
                zoomType: 'x',
                renderTo: place,
                reflow: true
            },
            title: {
                text: title
            },
            subtitle: {
                text: subTitle  
               // text: 'הקלק ומשוך על מנת להתמקד בטווח מסויים'
            },
            credits : {
            enabled: false
            },
            xAxis: {      
                type: 'datetime',
                ordinal: false,
                  labels: {
                        formatter: function() {
                                return Highcharts.dateFormat('%e. %b', this.value*1000); // milliseconds not seconds
                        }
                 }
           },
            yAxis: {
                title: {
                    text: yTitle
                }, 
                min: 0,
                minTickInterval: 1
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 1,
                useHTML: true
            },            
            plotOptions: {
               spline: {
                    marker: {
                        enabled: true
                    }
                },
                
                area: {

                    marker: {
                        radius: 2,
                        enabled: true
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    dataLabels: {
                        useHTML:true
                    },
                    threshold: 0                 
                }
            },
            
            
         tooltip: {
             
             formatter: function () {
                return '<b>' + this.series.name + '</b><br>' 
                        + Highcharts.dateFormat('%e. %b', this.x*1000) + ': ' + this.y ;
            },
            useHTML: true
        },
        drilldown: {
            drillUpButton: {
                relativeTo: 'plotBox',
                position: {
                    y: 0,
                    x: 0
                },
                theme: {
                    style: {
                        margin: 0,
                        padding: 0,
                        direction: 'ltr'
                    }                  
                }
            }},    
            series: series
        });   
   }   
   
 //-------------------------------------------- loader icon when ajax  ------------------------------------------------

    function startLoader() { 
        $('#users-body').empty();
        $('#taxa-body').empty();
        $('#projects-body').empty();        
        if (tabOn ===0)
             $('#graph-container').empty();
        else{
             $('#maps').removeClass('in');
             $('#maps').addClass('out');
        }

        
        $('#users-body').addClass('loading');
        $('#taxa-body').addClass('loading');
        $('#main-panel-container').addClass('loading');    
        $('#projects-body').addClass('loading');
              
    };

//-------------------------------------------- first load of page  ------------------------------------------------

     _selectMenu(); 

    //-------------------------------------------- Handle graph menu button -----------------------------------------  
    
   $( "#action_menu" ).on( "mouseleave", function() {
        $( "#action_menu" ).css("display", "none");
    }); 
   
    $( "#graph-btn" ).on( "click", function() {
        $( "#action_menu" ).css("display", "block");
    });    
 
    $(".action_li a").on( "click", function() {
       
        $( "#action_menu" ).css("display", "none");
        $("dropdown-menu").css("display", "none");
    });
   
     //-------------------------------------------- Handle map -----------------------------------------  

    function _init_map(){
     
             markers = [];
            var mapOptions = {
                center: new google.maps.LatLng(0, 0),
		zoom: 0,
                mapTypeId: 'roadmap'
            };
              
    // Display a map on the page
     map = new google.maps.Map(document.getElementById("maps"), mapOptions);
     
     $("#maps").append("<div id=\"legend\"></div>");
    
    }
    
     function _clear_markers(){
         for (var i = 0; i < markers.length; i++ ) {
             markers[i].setMap(null);
         }
        markers = [];
     }
    
     function _update_map(){
        
         google.maps.event.trigger(map,'resize');
         
         if (markers.length > 0)
             _clear_markers();
             
        bounds = new google.maps.LatLngBounds();
        
        // Info Window Content
        var infoWindowContent = [];
      

        var infoWindow = new google.maps.InfoWindow();
        var marker, i,taxIcon;

        for (i = 0; i < filterd_obs.length; i++) {  
            var position = new google.maps.LatLng( filterd_obs[i].latitude,filterd_obs[i].longitude);
            taxIcon = marker_selector(filterd_obs[i].icon_name);
            bounds.extend(position);
            marker = new google.maps.Marker({
                position: position,
                map: map,
                icon: taxIcon
    //            title: filterd_obs[i].id
            });
            markers.push(marker);
            var userImg = filterd_obs[i].user_photo;

            if(!userImg)
                    userImg = 'assets/images/persons.png';

            var taxaImg = filterd_obs[i].taxon_photo;

            if(!taxaImg)
                    taxaImg = 'assets/images/persons.png';

           infoWindowContent.push( 
            '<div class="info_content">' +
                '<div class=\"info-title\"> נצפה בתאריך: ' + filterd_obs[i].observed_on + '</div>' 
                + "<ul class=\"info-main\">"
                    + "<li class=\"media\">" 
                        + "<div class=\"img_title\">זן:</div>"
                        + "<img src=\""+ taxaImg + "\">"
                        + "<div class=\"tax_name truncate-name\">" + filterd_obs[i].taxon_name + "</div>"
                    + "</li>"     
                    + "<li class=\"media\">" 
                        + "<div class=\"img_title\">מתצפת:</div>"
                        + "<img src=\""+ userImg + "\">"
                        + "<div class=\"user_name truncate-name\">" + filterd_obs[i].user_name + "</div>"
                    + "</li>" 

                + "</ul>"
            +'</div>');

            // Allow each marker to have an info window    
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infoWindow.setContent(infoWindowContent[i]);
                    infoWindow.open(map, marker);
                };
            })(marker, i));


         
    }
    
    
            // Automatically center the map fitting all markers on the screen
            map.fitBounds(bounds);

     map.controls[google.maps.ControlPosition.RIGHT_TOP].push(legend_builder());

    }
    
     function _update_map_for_seassions(){
        
         google.maps.event.trigger(map,'resize');
         
         if (markers.length > 0)
             _clear_markers();
             
        bounds = new google.maps.LatLngBounds();
        
        // Info Window Content
        var infoWindowContent = [];
      
        // build legend
        var legend = document.getElementById('legend');
        while (legend.firstChild) {
            legend.removeChild(legend.firstChild);
            }
       legend.innerHTML = "<div style=\"padding-bottom: 5px;\" class=\"info-upper-panel\">מקרא<button id=\"legend-helper\" type=\"button\" class=\"btn btn-default btn-xs\"><i class=\"fa fa-times-circle\" aria-hidden=\"true\"></i></button></div>";   


        var infoWindow = new google.maps.InfoWindow();
        var marker, i,taxIcon;
        var index = 0 ; 
        var markerCounter = 0 ;
      
        for (var year in selectedSeassionsObs[selectedSeassion.attr('id')]){   
            var ob = selectedSeassionsObs[selectedSeassion.attr('id')][year]['observation'];             
            if (ob.length >= 1)
            {              
            taxIcon = marker_selector_for_seassions(index);
            var div = document.createElement('div');
            div.className = 'legend-items';
            div.innerHTML = '<img src="' + taxIcon + '"> ' + converateSeassionName(selectedSeassion.attr('id')) + ' ' + year; 
            legend.appendChild(div);
            for (i = 0; i < ob.length; i++) {  
                var position = new google.maps.LatLng( ob[i].latitude,ob[i].longitude);
                bounds.extend(position);
                marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    icon: taxIcon,
                    title: converateSeassionName(selectedSeassion.attr('id')) + ' ' + year
                });
                markers.push(marker);
                var userImg = ob[i].user_photo;

                if(!userImg)
                        userImg = 'assets/images/persons.png';

                var taxaImg = ob[i].taxon_photo;

                if(!taxaImg)
                        taxaImg = 'assets/images/persons.png';

               infoWindowContent.push( 
                '<div class="info_content">' +
                    '<div class=\"info-title\"> נצפה בתאריך: ' + ob[i].observed_on + '</div>' 
                    + "<ul class=\"info-main\">"
                        + "<li class=\"media\">" 
                            + "<div class=\"img_title\">זן:</div>"
                            + "<img src=\""+ taxaImg + "\">"
                            + "<div class=\"tax_name truncate-name\">" + ob[i].taxon_name + "</div>"
                        + "</li>"     
                        + "<li class=\"media\">" 
                            + "<div class=\"img_title\">מתצפת:</div>"
                            + "<img src=\""+ userImg + "\">"
                            + "<div class=\"user_name truncate-name\">" + ob[i].user_name + "</div>"
                        + "</li>" 

                    + "</ul>"
                +'</div>');

                // Allow each marker to have an info window    
                google.maps.event.addListener(marker, 'click', (function(marker, markerCounter) {
                    return function() {
                        infoWindow.setContent(infoWindowContent[markerCounter]);
                        infoWindow.open(map, marker);
                    };
                })(marker, markerCounter));
                markerCounter++;
             }
             index++;
            }
         }
            // Automatically center the map fitting all markers on the screen
            map.fitBounds(bounds);

            map.controls[google.maps.ControlPosition.RIGHT_TOP].push(legend);

    }
    
     function marker_selector(taxIcon){
          switch(taxIcon) {             
            case "Mammalia":
                return 'assets/images/red.png';         
            case "Aves":
                return 'assets/images/yellow.png'
            case "Reptilia":
                return 'assets/images/pink.png'
            case "Arachnida":
                return 'assets/images/black.png'
            case "Insecta":
                return 'assets/images/orange.png'
            case "Plantae":
                return 'assets/images/green.png'
            case "Amphibia":
                return 'assets/images/white.png'
            case "Animalia":
                return 'assets/images/blue.png'
            case "Fungi":
                return 'assets/images/purple.png'
            case "Mollusca":
                return 'assets/images/ltblue.png'
          }
    }
    
    function marker_selector_for_seassions(index){
          switch(index) {             
            case 0:
                return 'assets/images/red.png';         
            case 1:
                return 'assets/images/yellow.png'
            case 2:
                return 'assets/images/pink.png'
            case 3:
                return 'assets/images/blue.png'
            case 4:
                return 'assets/images/orange.png'
          }
    }
    
    function legend_builder(){
         
        var icons = [
          {
            name: "יונקים",
            icon: marker_selector("Mammalia")
          },
           {
            name: "עופות",
            icon: marker_selector("Aves")
          },
           {
            name: "זוחלים",
            icon: marker_selector("Reptilia")
          },
           {
            name: "פרוקי רגליים",
            icon: marker_selector("Arachnida")
          },
           {
            name: "זוחלים",
            icon: marker_selector("Insecta")
          },
           {
            name: "צמחים",
            icon: marker_selector("Plantae")
          },
           {
            name: "דו חיים",
            icon: marker_selector("Amphibia")
          },
           {
            name: "חיות",
            icon: marker_selector("Animalia")
          },
           {
            name: "פטריות",
            icon: marker_selector("Fungi")
          },
           {
            name: "רכיכות",
            icon: marker_selector("Mollusca")
          }
          
        ];
        
        var legend = document.getElementById('legend');
        while (legend.firstChild) {
            legend.removeChild(legend.firstChild);
            }
        legend.innerHTML = "<div style=\"padding-bottom: 5px;\" class=\"info-upper-panel\">מקרא<button id=\"legend-helper\" type=\"button\" class=\"btn btn-default btn-xs\"><i class=\"fa fa-times-circle\" aria-hidden=\"true\"></i></button></div>";   
        for (var key in icons) {
            var type = icons[key];
            var name = type.name;
            var icon = type.icon;
            var div = document.createElement('div');
            div.className = 'legend-items';
            div.innerHTML = '<img src="' + icon + '"> ' + name;             
            legend.appendChild(div);
        }
        return legend;
    }
    
    function converateIconName(taxIcon) {
           switch(taxIcon) {             
            case "Mammalia":
                return 'יונקים';         
            case "Aves":
                return 'עופות';
            case "Reptilia":
                return 'זוחלים';
            case "Arachnida":
                return 'פרוקי רגליים';
            case "Insecta":
                return 'חרקים';
            case "Plantae":
                return 'צמחים';
            case "Amphibia":
                return 'דו חיים';
            case "Animalia":
                return 'חיות';
            case "Fungi":
                return 'פטריות';
            case "Mollusca":
                return 'רכיכות';
          }
    }

    
    $('#graph-tab').on('click', function(){
        tabOn = 0;
        $('#graphs').toggleClass('in out');
        $('#maps').toggleClass('in out');
    });
    
    $('#map-tab').on('click', function(){
        tabOn = 1;
            $('#graphs').toggleClass('in out');
            $('#maps').toggleClass('in out');
            if (selectedSeassion !== null)
                _update_map_for_seassions();
            else
                _update_map();
    });
    
    
     $('#user-checkbox').click(function () {
            
         
        if (!($(this).attr('checked'))) 
        {            
             $(this).attr("disabled", true);
             selectedUsers = [];
            _selectMenu();
        }
        if (selectedUsers === undefined || selectedUsers.length === 0){
          $('#user-search').show();
       }         
       else{
           $('#user-search').hide();
       }  
    });
    
     $('#taxon-checkbox').click(function () {
               
         if (!($(this).attr('checked'))) 
        {            
             $(this).attr("disabled", true);
             for (var key in filterd_taxons) {
                var obj = filterd_taxons[key];
                if($.inArray(parseInt(obj.taxons.id), selectedTaxs)>-1)   {           
                    selectedTaxs.splice( selectedTaxs.indexOf( parseInt(obj.taxons.id)) , 1);                
                }
            }           
            _selectMenu();
        }
        if (selectedTaxs === undefined || selectedTaxs.length === 0){
          $('#taxon-search').show();
        }         
       else{
           $('#taxon-search').hide();
       }   
    });
    
     
    
     $('#resize-btn').click(function () {
  
          
          
         if (resizeOn === 0) {
             
           var taxHeight = $( "#taxa-list" ).height();
           $( "#taxa-list" ).height(taxHeight);                   
           $( "#taxa-list" ).addClass('slide-close-taxa'); 
           $('.t-hide').fadeOut('fast');
           $( "#taxa-list" ).append("<button id=\"taxons-btn\" type=\"button\" class=\"btn btn-default btn-xs slide-btn\">" +
                            "<i class=\"fa fa-arrow-circle-right fa-2x\" aria-hidden=\"true\"></i>" +
                        "</button>" +
                        "<p id=\"t-text\" class=\"vertical-text\">זנים</p>"
                     
                     );
           
           var projectHeight = $( "#project-list" ).height();
           $( "#project-list" ).height(projectHeight);          
           $( "#project-list" ).addClass('slide-close-project');   
           $('.p-hide').fadeOut('fast');
            $( "#project-list" ).append("<button id=\"projects-btn\" type=\"button\" class=\"btn btn-default btn-xs slide-btn\">" +
                            "<i class=\"fa fa-arrow-circle-left fa-2x\" aria-hidden=\"true\"></i>" +
                        "</button>" +
                        "<p id=\"p-text\" class=\"vertical-text\">פרוייקטים</p>");
           
           var userHeight = $( "#user-list" ).height();
           $( "#user-list" ).height(userHeight);          
           $( "#user-list" ).addClass('slide-close-user');
           $('.u-hide').fadeOut('fast');
            $( "#user-list" ).append("<button id=\"users-btn\" type=\"button\" class=\"btn btn-default btn-xs slide-btn\">" +
                            "<i class=\"fa fa-arrow-circle-left fa-2x\" aria-hidden=\"true\"></i>" +
                        "</button>" 
                        +"<p id=\"u-text\" class=\"vertical-text\">משתמשים</p>");
           
//           $('#right-main').removeClass('col-lg-3');
           $('#right-main').addClass('shrink');
           
//           $('#left-main').removeClass('col-lg-3');
           $('#left-main').addClass('shrink');
           
            var countE = 0 ;
            var IntervalExpand = setInterval(function() {
                if (tabOn===0)
                     $("#graph-container").highcharts().reflow();
               else{
                    google.maps.event.trigger(map,'resize');
                    map.fitBounds(bounds);
               }
             $('#main-resize').one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                  countE++;

              });
              $('#right-main').one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                  countE++;

              });
             $('#left-main').one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                  countE++;

              });
              if (countE >=3)
                  clearInterval(IntervalExpand);
             }, 0.5);

           
//           $('#main-resize').removeClass('col-lg-6');
           $('#main-resize').addClass('expand');   
           $('#row-sums').addClass('row-main');
           $('#row-main').addClass('row-main');
            
           resizeOn = 1;
                  
         }else 
         {
           
            var countM = 0 ;
            var IntervalMinimize = setInterval(function() {
                if (tabOn===0)
                $("#graph-container").highcharts().reflow();
               else{
                google.maps.event.trigger(map,'resize');
                map.fitBounds(bounds);
             }
             $('#main-resize').one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                  countM++;

              });
              $('#right-main').one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                  countM++;

              });
             $('#left-main').one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                  countM++;

              });
              if (countM >=3)
                  clearInterval(IntervalMinimize);
             }, 0.5);  
             
           $('#projects-btn').remove();
           $('#users-btn').remove();
           $('#taxons-btn').remove();
           $('#p-text').remove();
           $('#u-text').remove();
           $('#t-text').remove();           
           $('#row-sums').removeClass('row-main');
           $('#row-main').removeClass('row-main');
           $('#main-resize').removeClass('expand');
           $('#main-resize').addClass('col-lg-6');   
             
             
           $( "#taxa-list" ).removeClass('slide-close-taxa');           
           $('#left-main').removeClass('shrink');
           $('#left-main').addClass('col-lg-3');          
           $('.t-hide').fadeIn('fast'); 
        

           $('#right-main').removeClass('shrink');          
           $( "#project-list" ).removeClass('slide-close-project'); 
           $( "#user-list" ).removeClass('slide-close-user');          
           $('#right-main').addClass('col-lg-3');
           $('.p-hide').fadeIn('fast');         
           $('.u-hide').fadeIn('fast'); 
           
           resizeOn = 0;
         }
         
          
     });
     
     $("#taxa-list").on("click", "#taxons-btn", function(){
           $( "#taxa-list" ).addClass('open'); 
        $( "#taxa-list" ).removeClass('slide-close-taxa');           
           $('#left-main').removeClass('shrink');
           $('#left-main').addClass('col-lg-3');          
           $('.t-hide').fadeIn('fast'); 
           $( "#taxons-btn" ).hide();
           $('#t-text').hide(); 
        });
        
        
         $("#taxa-list").on( "mouseleave", function() {
          
              
          if ($("#taxa-list").hasClass('open')){
             $('#left-main').addClass('fast-close');   
             $( "#taxa-list" ).addClass('fast-close'); 
             $(this).one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
           
                     $('#left-main').removeClass('fast-close');   
                     $( "#taxa-list" ).removeClass('fast-close');
                 });                          
            $('#left-main').addClass('shrink');                         
            $('#left-main').removeClass('col-lg-3');
   
            $( "#taxa-list" ).addClass('slide-close-taxa');
            $('.t-hide').hide();
            $( "#taxons-btn" ).show();
            $('#t-text').show(); 
        
            $( "#taxa-list" ).removeClass('open'); 
        }      
        });    
        
     $("#user-list").on("click", "#users-btn", function(){
          $( "#user-list" ).addClass('open'); 
          $( "#project-list" ).addClass('stay-shrink');
          var style = document.createElement('style');
          style.type = 'text/css';
          style.innerHTML = '.cssClass { width: ' + $( "#project-list" )[0].getBoundingClientRect().width + 'px !important; }';         
          document.getElementsByTagName('head')[0].appendChild(style);
          $('#project-list').addClass('cssClass');  
          
          $( "#users-btn" ).hide();  
          $('#u-text').hide(); 
          $( "#user-list" ).removeClass('slide-close-user');  
          $('#right-main').removeClass('shrink');                         
          $('#right-main').addClass('col-lg-3');
          $('.u-hide').fadeIn('fast'); 
     });

      $("#user-list").on( "mouseleave", function() {
          
          if ($("#project-list").hasClass('open'))
              return false;
              
          if ($("#user-list").hasClass('open')){
             $('#right-main').addClass('fast-close');   
             $( "#user-list" ).addClass('fast-close'); 
             $(this).one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                     if ($("#user-list").hasClass('open'))
              return false;
                     $('#right-main').removeClass('fast-close');   
                     $( "#user-list" ).removeClass('fast-close');
                     $( "#project-list" ).removeClass('stay-shrink'); 
                     $('#project-list').removeClass('cssClass'); 
                     document.body.classList.remove('cssClass');
                 });                          
            $('#right-main').addClass('shrink');                         
            $('#right-main').removeClass('col-lg-3');
   
            $( "#user-list" ).addClass('slide-close-user');
            $('.u-hide').hide();
            $( "#users-btn" ).show();
            $('#u-text').show(); 
            $( "#user-list" ).removeClass('open'); 
        }      
        });    
    
     $("#project-list").on("click", "#projects-btn", function(){
          $( "#project-list" ).addClass('open'); 
          $( "#user-list" ).addClass('stay-shrink');
          var style = document.createElement('style');
          style.type = 'text/css';
          style.innerHTML = '.cssClass { width: ' + $( "#user-list" )[0].getBoundingClientRect().width + 'px !important; }';         
          document.getElementsByTagName('head')[0].appendChild(style);
          $('#user-list').addClass('cssClass');                  
           
          $( "#projects-btn" ).hide(); 
          $('#p-text').hide(); 
          $( "#project-list" ).removeClass('slide-close-project');  
          $('#right-main').removeClass('shrink');                         
          $('#right-main').addClass('col-lg-3');
          $('.p-hide').fadeIn('fast');         
     });
 
      $("#project-list").on( "mouseleave", function() {
          
          if ($("#user-list").hasClass('open'))
              return false;
          
          if ($("#project-list").hasClass('open')){
             $('#right-main').addClass('fast-close');   
             $( "#project-list" ).addClass('fast-close'); 
              $(this).one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend"
              ,function(e){
                   if ($("#project-list").hasClass('open'))
              return false;
                     $('#right-main').removeClass('fast-close');   
                     $( "#project-list" ).removeClass('fast-close'); 
                     $( "#user-list" ).removeClass('stay-shrink'); 
                     $('#user-list').removeClass('cssClass');  
                     document.body.classList.remove('cssClass');
                 });                          
            $('#right-main').addClass('shrink');                         
            $('#right-main').removeClass('col-lg-3');
   
            $( "#project-list" ).addClass('slide-close-project');
            $('.p-hide').fadeOut('fast');
            $( "#projects-btn" ).show();
            $('#p-text').show(); 
            $( "#project-list" ).removeClass('open'); 
        }      
        });
        
    $('#legend').on('click', '#legend-helper' , function(){
        
        if ($('#legend-helper .fa').hasClass( "fa-times-circle" ))
        {
            $('.legend-items').css("display", "none");
        } else
        {
            $('.legend-items').css("display", "block");
        }
        
        $('#legend-helper .fa').toggleClass('fa-times-circle fa-info-circle');
       
    });       

});



              