jQuery(document).ready(function(){
	jQuery('body').on('click','.resetuserdata',function(){
	var userId  =  jQuery(this).attr('data-id');
	if(confirm('Are you sure you want to reset login count')){
		jQuery.ajax({
		type: "post",
         dataType:'json',
         data: "action=ulc_reset_counter&type="+rwbObj.nonce+"&userId="+userId,
         url: rwbObj.ajaxurl,       
          success: function( data ) {
           if(data.msg == 'sucess'){    
			location.reload(); 
		  }
		}
      });
	}	
  });
});