(function($) {
  "use strict";
    jQuery(document).ready(function($){
      function cf7_sendFileToServer(formData,status,obj){
        var uploadURL = superaddons_products_uploads.ajax_url; //Upload URL
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
              if( data.status =="ok" ) {
                  status.name_upload(data.text);
                  var name = $("#woo_products_upload_files").val();
                if(name == "" ) {
                    name = data.text;
                }else{
                  name = name +"|"+data.text;
                }
                $("#woo_products_upload_files").val(name); 
              }else{
                  if( !data.text ) {
                    status.text("Error:");
                  }else{
                      status.text(data.text);
                  }
              }
          }
          }); 
        status.setAbort(jqXHR);
      }
      var rowCount=0;
      function cf7_createStatusbar(obj){
         rowCount++;
         var row="odd";
         if(rowCount %2 ==0) row ="even";
           this.statusbar = jQuery("<div class='products-uploads-drop-statusbar "+row+"'></div>");
           this.type = jQuery("<div class='products-uploads-drop-type'></div>").appendTo(this.statusbar);
           this.type_file = jQuery("<div class='products-uploads-drop-type_file'></div>").appendTo(this.statusbar);
           this.img = jQuery('<div style="display:none" class="products-uploads-drop-img" ></div>').appendTo(this.statusbar);
           this.filename = jQuery("<span class='products-uploads-drop-filename'></span>").appendTo(this.statusbar);
           this.size = jQuery("<span class='products-uploads-drop-filesize'></span>").appendTo(this.statusbar);
           this.abort = jQuery('<div class="products-uploads-drop-abort"></div>').appendTo(this.statusbar);
           this.remove = jQuery('<div style="display:none" class="products-uploads_file_upload_remove" ></div>').appendTo(this.statusbar);
           this.progressBar = jQuery("<div class='products-uploads-drop-progressBar'><div></div></div>").appendTo(this.statusbar);
           this.error = jQuery("<div class='products-uploads-drop-text-error'></div>").appendTo(this.statusbar);
           obj.after(this.statusbar);
           this.text = function(txt){   
                this.error.addClass("products-uploads-text-error").html(txt);
                this.progressBar.addClass("products-uploads-text-error-pro");
          }
          this.name_upload = function(txt){   
                this.remove.attr('data-name', txt);
          }
          this.setFileSize = function(txt){   
                this.type_file.html(txt);
          }
          this.setFileImg = function(txt){   
            this.img.html('<img src="'+txt+'" />');
            this.img.css("display","inline-block");
            this.type.css("display","none","important");
            this.type_file.css("display","none","important");
          }
          this.setFileNameSize = function(name,size){
            var sizeStr="";
            var sizeKB = size/1024;
            if(parseInt(sizeKB) > 1024){
              var sizeMB = sizeKB/1024;
              sizeStr = "("+sizeMB.toFixed(2)+" MB)";
            }
            else{
              sizeStr = "("+sizeKB.toFixed(2)+" KB)";
            }
            this.filename.html(name);
            this.size.html(sizeStr);
          }
          this.setProgress = function(progress){   
            var progressBarWidth =progress*this.progressBar.width()/ 100; 
            console.log(this.progressBar); 
            console.log(progressBarWidth); 
            this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "%&nbsp;");
            if(parseInt(progress) >= 100){
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
      function upload_file(file,obj){
        const validImageTypes = ['gif', 'jpeg','jpg', 'png','webp'];
        var fd = new FormData();
        fd.append('file', file);
        fd.append('size', obj.data("size") );
        fd.append('type', obj.data("type") );
        fd.append('nonce', superaddons_products_uploads.nonce );
        fd.append('action', "superaddons_products_uploads" );
        var status = new cf7_createStatusbar(obj); //Using this we can set progress.
        var file_type = file.name.split('.');
        file_type = file_type.slice(-1).pop()
        status.setFileNameSize(file.name,file.size);
        status.setFileSize(file_type);
        const fileReader = new FileReader()
        if (validImageTypes.includes(file_type)) {
            fileReader.readAsDataURL(file);
            fileReader.onload = function() {
              const url = fileReader.result
              status.setFileImg(url);
            }
        }
        cf7_sendFileToServer(fd,status,obj);
      }
      function cf7_handleFileUpload(files,obj){
        var max = obj.attr("data-max");
        var name = $("#woo_products_upload_files").val();
        var count = (name.match(/\|/g) || []).length;
        if(name != "" ){
            count++;
        }
        if( max == "" ){
          max = 0;
        }
        var max_limit = max - count + 1;
        if ( max != 0 && parseInt(files.length)>=max_limit){
            alert(superaddons_products_uploads.text_maximum +" "+max);
          }else{
           for (var i = 0; i < files.length; i++) {
            setTimeout(upload_file(files[i],obj), 500);
           }
        }
      }
      var obj = $(".products-uploads-dragandrophandler");
      obj.on('dragenter', function (e) {
        e.stopPropagation();
        e.preventDefault();
        $(this).css('border', '2px solid #0B85A1');
      });
      obj.on('dragover', function (e) 
      {
         e.stopPropagation();
         e.preventDefault();
      });
      obj.on('drop', function (e) {
        obj= $(this);
         $(this).css('border', '2px dotted #0B85A1');
         e.preventDefault();
         var files = e.originalEvent.dataTransfer.files;
         //We need to send dropped files to Server
         cf7_handleFileUpload(files,obj);
      });
      $(document).on('dragenter', function (e) {
        e.stopPropagation();
        e.preventDefault();
      });
      $(document).on('dragover', function (e) {
        e.stopPropagation();
        e.preventDefault();
        obj.css('border', '2px dotted #0B85A1');
      });
      $(document).on('drop', function (e) {
        e.stopPropagation();
        e.preventDefault();
      });
      obj.find('a').on('click', function (e) {
        e.preventDefault();
        $(this).closest(".products-uploads-dragandrophandler-container").find('.input-uploads').click();
      });
       obj.closest(".products-uploads-dragandrophandler-container").find('.input-uploads').on('change', function (e) {
         var files = this.files;
         //We need to send dropped files to Server
         obj= $(this).closest('.products-uploads-dragandrophandler');
         cf7_handleFileUpload(files,obj);
      });
      $("body").on("click",".products-uploads_file_upload_remove",function(e){
         e.preventDefault();
         var cr_name = $(this).data("name") ;
         var data = {
          'action': 'superaddons_products_uploads_remove',
          'name' : cr_name,
          'nonce': superaddons_products_uploads.nonce
        };
        var name = $('#woo_products_upload_files').val().split("|");
        for (var i=name.length-1; i>=0; i--) {
            if (name[i] === cr_name) {
                name.splice(i, 1);
            }
        }
         $('#woo_sing_upload_files').val(name.join("|"));
         $(this).closest('.products-uploads-drop-statusbar').remove();
         jQuery.post(superaddons_products_uploads.ajax_url, data, function(response) {
        });
         return false;
      })
    })
  })(jQuery);
