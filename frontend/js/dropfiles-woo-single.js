function woo_single_sendFileToServer(formData,status)
{
  //var uploadURL = woo-single_dropfiles.url_plugin + "frontend/upload.php"; //Upload URL
  var uploadURL = woo_single_dropfiles.ajax_url; //Upload URL
  var extraData ={}; //Extra Data.
  var jqXHR=jQuery.ajax({
          xhr: function() {
            var xhrobj = jQuery.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        //Set progress
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
      url: uploadURL,
      type: "POST",
    contentType:false,
    processData: false,
        cache: false,
        data: formData,
        success: function(data){
          status.setProgress(100);
          data = JSON.parse(data);
          console.log(data);
          console.log(data.status);
          if( data.status =="ok" ) {
              status.name_upload(data.text);
              var name = jQuery(".woo-single-drop-upload").val();
            if(name == "" ) {
                name = data.text;
            }else{
               name = name +"|"+data.text;
            }
            jQuery(".woo-single-drop-upload").val(name); 
          }else{
              if( !data.text ) {
                //alert(data);
                status.text("Error: POST Content-Length limit");
              }else{
                  status.text(data.text);
              }
              
          }
                
    }
    }); 

  status.setAbort(jqXHR);
}

var rowCount=0;
function woo_single_createStatusbar(obj){
   rowCount++;
   var row="odd";
   if(rowCount %2 ==0) row ="even";



     this.statusbar = jQuery("<div class='woo-single-drop-statusbar "+row+"'></div>");
     this.type = jQuery("<div class='woo-single-drop-type'></div>").appendTo(this.statusbar);
     this.type_file = jQuery("<div class='woo-single-drop-type_file'></div>").appendTo(this.statusbar);
     this.filename = jQuery("<div class='woo-single-drop-filename'></div>").appendTo(this.statusbar);
     this.size = jQuery("<div class='woo-single-drop-filesize'></div>").appendTo(this.statusbar);
     
     this.abort = jQuery('<div class="woo-single-drop-abort"></div>').appendTo(this.statusbar);
     this.remove = jQuery('<div style="display:none" class="woo-single-drop-remove"></div>').appendTo(this.statusbar);
     this.progressBar = jQuery("<div class='woo-single-drop-progressBar'><div></div></div>").appendTo(this.statusbar);
     obj.after(this.statusbar);
    
     this.text = function(txt){   
          this.progressBar.addClass("woo-single-text-error").html(txt);
          this.remove.hide();
    }
     this.name_upload = function(txt){   
          this.remove.attr('data-name', txt);
    }
    this.setFileSize = function(txt){   
          this.type_file.html(txt);
    }
    this.setFileNameSize = function(name,size)
    {
      var sizeStr="";
      var sizeKB = size/1024;
      if(parseInt(sizeKB) > 1024)
      {
        var sizeMB = sizeKB/1024;
        sizeStr = "("+sizeMB.toFixed(2)+" MB)";
      }
      else
      {
        sizeStr = "("+sizeKB.toFixed(2)+" KB)";
      }
    
      this.filename.html(name);
      this.size.html(sizeStr);
    }
    this.setProgress = function(progress){   
      var progressBarWidth =progress*this.progressBar.width()/ 100;  
      this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "%&nbsp;");
      if(parseInt(progress) >= 100)
      {
        this.abort.hide();
        this.remove.show();
      }
    }

  this.setAbort = function(jqxhr){
    var sb = this.statusbar;
    this.abort.click(function()
    {
      jqxhr.abort();
      sb.hide();
    });
  }
}
function woo_single_handleFileUpload(files,obj)
{
  var max = obj.attr("data-max");
  var name = jQuery(".woo-single-drop-upload").val();
  var count = (name.match(/\|/g) || []).length;
  if(name != "" ){
      count++;
  }
  if( max == "" ){
    max = 0;
  }
  var max_limit = max - count + 1;
  if ( max != 0 && parseInt(files.length)>=max_limit){
      alert("You can only upload a maximum of "+max+" files");
    }else{
        for (var i = 0; i < files.length; i++) {
          var fd = new FormData();
          fd.append('file', files[i]);
          fd.append('id', obj.data("id") );
          fd.append('action', "woo_single_dropfiles_upload" );
          var status = new woo_single_createStatusbar(obj); //Using this we can set progress.
          var file_type = files[i].name.split('.');
          status.setFileNameSize(files[i].name,files[i].size);
          status.setFileSize(file_type.slice(-1).pop());
          woo_single_sendFileToServer(fd,status);
       
       }
    }
   
}
jQuery(document).ready(function($){

  var obj = jQuery(".woo-single-dragandrophandler");
  obj.on('dragenter', function (e) 
  {
    e.stopPropagation();
    e.preventDefault();
    jQuery(this).css('border', '2px solid #0B85A1');
  });
  obj.on('dragover', function (e) 
  {
     e.stopPropagation();
     e.preventDefault();
  });
  obj.on('drop', function (e) 
  {
    
     jQuery(this).css('border', '2px dotted #0B85A1');
     e.preventDefault();
     var files = e.originalEvent.dataTransfer.files;
     //We need to send dropped files to Server
     woo_single_handleFileUpload(files,obj);
  });
  jQuery(document).on('dragenter', function (e) 
  {
    e.stopPropagation();
    e.preventDefault();
  });
  jQuery(document).on('dragover', function (e) 
  {
    e.stopPropagation();
    e.preventDefault();
    obj.css('border', '2px dotted #0B85A1');
  });
  jQuery(document).on('drop', function (e) 
  {
    e.stopPropagation();
    e.preventDefault();
  });
  obj.find('a').on('click', function (e) 
  {
    //e.stopPropagation();
    e.preventDefault();
    $(this).closest(".woo-single-dragandrophandler-container").find('.input-uploads').click();
  });

   obj.closest(".woo-single-dragandrophandler-container").find('.input-uploads').on('change', function (e) 
  {
     var files = this.files;
     //We need to send dropped files to Server
     woo_single_handleFileUpload(files,obj);
  });

  $("body").on("click",".woo-single-drop-remove",function(e){
     e.preventDefault();
     var cr_name = $(this).data("name") ;
     var data = {
      'action': 'woo_single_dropfiles_remove',
      'name': cr_name
    };
    var name = $('#woo_sing_upload_files').val();
    var new_name = name.replace("|"+cr_name, "");
    new_name = new_name.replace(cr_name, "");
     $('#woo_sing_upload_files').val(new_name);
     $(this).closest('.woo-single-drop-statusbar').remove();
     jQuery.post(woo_single_dropfiles.ajax_url, data, function(response) {
      
    });
     
  })
});