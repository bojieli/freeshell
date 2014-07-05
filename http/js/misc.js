function loginanimation()
{
	$("#loginbutton").animate({width:"550px"},function(data){
				$("#logininput").fadeIn();
				if($.browser.msie)
				{
					if($.browser.version<=8)
						$("#loginconfirm").css("background-image","url('img/next2_ie7.png')");
					else
						$("#loginconfirm").css("background-image","url('img/next2.png')");
				}
				else
				{
					$("#loginconfirm").css("background-image","url('img/next2.png')");
				}
				//$("#loginconfirm").css("background-image","url('img/next2.png')");
				$("#logintext").addClass("loginunderline");
				$("#loginconfirm").bind('click',function(){
						$("#loginform").submit();
					});
				$("#welcome,#banner").bind('click',function(e){
					$("#logininput").fadeOut(function(e){
						$("#loginconfirm").unbind('click');
						$("#loginbutton").animate({width:"150px"});
						if($.browser.msie)
						{
							if($.browser.version<=8)
								$("#loginconfirm").css("background-image","url('img/next_ie7.png')");
							else
								$("#loginconfirm").css("background-image","url('img/next.png')");
						}
						else
						{
							$("#loginconfirm").css("background-image","url('img/next.png')");
						}
						$("#welcome,#banner").unbind('click');
						$("#logintext").removeClass("loginunderline");
						//$("#loginconfirm").css("background-color","#C44D58");
					});
				});
			});
}
$(document).ready(function(){
	if(!$.browser.msie||($.browser.msie&&$.browser.version>=8))
	{
	    $(".slideshow").jCarouselLite({
	        btnNext: ".lastline",
	        btnPrev: ".prev",
			auto:20000,
			start:0,
			speed:1000,
			vertical:false,
			easing:"easeInOutExpo",
			btnGo:
	            [".1", ".2",
	            ".3"],
			afterEnd: function(a, to, btnGo) 
				{
					go=(($(a).attr("class")).substr(5,1));
	                $(".titlehighlighted").removeClass("titlehighlighted");
	                $("."+go).addClass("titlehighlighted");
	            }
	    });
	}
	//setTimeout(loginanimation(),5000);
	$("#loginbutton").bind('click',function(e){
		loginanimation();
	});
	$("#regbutton").bind('click',function(e){
		//$("#wrapper").animate({height:"2000px"},1000,function(){});
		$("#regplace").show(0);
		$('html, body').stop().animate({
	         	scrollTop: $("#regplace").offset().top
	 			}, 1500,'easeInOutExpo'/*,function(){$("#servstate,#welcome,#header").fadeOut()}*/);
		$("#regsend").bind('click',function(data){
                console.log(regdata);
                if ($('#distribution').val() == 'gallery' && ! $('input[name=gallery-id]:checked', '#regform').val()) {
                    $('#galleryfail').html('Please select one item from the gallery below!');
                }
				else if(regdata.email&&regdata.pass&&regdata.host) {
					$("#regform").submit();
                }
				else
				{
                    verify('email', $('#regemail').val());
                    checkpass($('#regpassword').val());
                    verify('host', $('#hostname').val());
                    setTimeout(function(){ $('#regbutton').click() }, 1000);
                    e.stopPropagation();
				}
		});
  						
	});
});
function subCheck()
{
    if(event.keyCode==13)
		$("#loginform").submit();
}
function regrecord()
{
	this.pass;
	this.email;
	this.host;
}
var passcheck=0;
var regdata=new regrecord();
var tmpdata;
function checkpass(type)
{
	if($("#regpassword").val()==$("#regconfpass").val()&&$("#regconfpass").val().length>=6)
	{
		$("#regpassword").css("border-color","#4ecd74");
		$("#regconfpass").css("border-color","#4ecd74");
		$("#passfail").html("Great. Your initial root password will be it, too.");
		regdata.pass=$("#regpassword").val();
		
	}
	else
	{
		$("#regpassword").css('border-color','#C44D58');
		$("#regconfpass").css("border-color","#C44D58");
		if($("#regpassword").val()!=$("#regconfpass").val())
		{
			$("#passfail").html("Password mismatch...");
		}
		if($("#regconfpass").val().length<6)
		{
			$("#passfail").html("Password too short...");
		}
	}
	passcheck+=1;
}
function verify(type,data)
{
	var errcode;
	switch(type)
	{
		case 'email': 
		{
			tmpdata=data;
			$.ajax({
				type:"get",
				url:"verify.php",
				async:true,
				data:{email:data},
				success:function(data){
					errcode=parseInt(data.substr(-1));
					if(data && !errcode)
					{
							$("#emailfail").html("This address will be confirmed later.");
							$("#regemail").css("border-color","#4ecd74");
							regdata.email=tmpdata;
					}
					else
					{
						$("#regemail").css("border-color","#C44D58");
						switch(errcode)
						{
							case 1:
							{
								$("#emailfail").html("Sorry, you have registered too many freeshells.");
								break;
							}
							case 2:
							{
								$("#emailfail").html("Invalid email format.");
								break;
							}
							case 3:
							{
								$("#emailfail").html("Not a USTC email. Sorry><");
								break;
							}
							case 4:
							{
								$("#emailfail").html("Email too long. Sorry><");
								break;
                            }
                            default:
                            {
                                $("#emailfail").html("Please input your USTC email.");
                                break;
                            }
						}
					}
				}
			});
			break;
		}
		case 'host': 
		{
			tmpdata=data;
			$.ajax({
				type:"get",
				url:"verify.php",
				async:true,
				data:{host:data},
				success:function(data){
					//alert(data);
					errcode=parseInt(data.substr(-1));
					if(data && !errcode)
					{
							$("#hostfail").html("Great hostname.");
							$("#hostname").css("border-color","#4ecd74");
							regdata.host=tmpdata;
					}
					else
					{
						$("#hostname").css("border-color","#C44D58");
						switch(errcode)
						{
							case 1:
							{
								$("#hostfail").html("Only lower-case letters, digits and '-' are allowed in hostname.");
								break;
							}
							case 2:
							{
								$("#hostfail").html("Sorry, this hostname has been taken.");
								break;
							}
							case 3:
							{
								$("#hostfail").html("Sorry, the hostname is too long.");
								break;
							}
							case 5:
							{
								$("#hostfail").html("Sorry, the hostname should be at least have 3 characters.");
								break;
							}
							case 6:
							{
								$("#hostfail").html("Sorry, this domain is reserved, please try another one.");
								break;
							}
                            default:
                            {
                                $("#hostfail").html("Please input your desired hostname.");
                                break;
                            }
						}
					}
				}
			});	
			break;
		}
	}
}
