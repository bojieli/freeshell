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
				if(regdata.name&&regdata.email&&regdata.pass&&regdata.folder&&regdata.title)
					$("#regform").submit();
				else
				{
					alert("Please complete your personal information.");
                    e.stopPropagation();
				}
		});
  						
	});
});
function checkaddr()
{
	$("#folderrepeat").html('So,your address will be located at http://'+$('#folderaddr').attr('value')+'.blog.ustc.edu.cn/');
}
function  subCheck()
{
    if(event.keyCode==13)
		$("#loginform").submit();
}
function regrecord()
{
	this.name;
	this.pass;
	this.email;
	this.folder;
	this.title;
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
		$("#passfail").html("Affirmative.");
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
		case 'name':
		{
			tmpdata=data;
			$.ajax({
				type:"get",
				url:"verify.php",
				async:true,
				data:{name:data},
				success:function(data){
					//alert(data);
					errcode=parseInt(data.substr(-1));
					if(!errcode)
					{
							$("#namefail").html("A good name.");
							$("#regname").css("border-color","#4ecd74");
							regdata.name=tmpdata;
					}
					else
					{
						$("#regname").css("border-color","#C44D58");
						switch(errcode)
						{
							case 1:
							{
								$("#namefail").html("Sorry, the username has been taken.");
								break;
							}
							case 2:
							{
								$("#namefail").html("Invalid username format.");
								break;
							}
							case 3:
							{
								$("#namefail").html("Sorry, the username is too long.");
								break;
							}
							case 4:
							{
								$("#namefail").html("Sorry, the username should have at least 3 characters.");
								break;
							}
						}
					}
				}
			});	
			break;
		}
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
					if(!errcode)
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
								$("#emailfail").html("Sorry, you have created too many blogs.");
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
						}
					}
				}
			});
			break;
		}
		case 'title': 
		{
			tmpdata=data;
			$.ajax({
				type:"get",
				url:"verify.php",
				async:true,
				data:{title:data},
				success:function(data){
					errcode=parseInt(data.substr(-1));
					if(!errcode)
					{
							$("#titlefail").html("Great blog title.");
							$("#regbtitle").css("border-color","#4ecd74");
							regdata.title=tmpdata;
					}
					else
					{
						$("#regbtitle").css("border-color","#C44D58");
					}
				}
			});
			break;
		}
		case 'folder': 
		{
			tmpdata=data;
			$.ajax({
				type:"get",
				url:"verify.php",
				async:true,
				data:{folder:data},
				success:function(data){
					//alert(data);
					errcode=parseInt(data.substr(-1));
					if(!errcode)
					{
							$("#folderfail").html("The domain will be assigned to your blog.");
							$("#folderaddr").css("border-color","#4ecd74");
							regdata.folder=tmpdata;
					}
					else
					{
						$("#folderaddr").css("border-color","#C44D58");
						switch(errcode)
						{
							case 1:
							{
								$("#folderfail").html("Only letters, digits and '-' are allowed in domain name.");
								break;
							}
							case 2:
							{
								$("#folderfail").html("Sorry, this domain name has been taken.");
								break;
							}
							case 3:
							{
								$("#folderfail").html("Sorry, the domain name is too long.");
								break;
							}
							case 4:
							{
								$("#folderfail").html("Sorry, only lower case letters (a-z) are allowed.");
								break;
							}
							case 5:
							{
								$("#folderfail").html("Sorry, the domain name should be at least have 3 characters.");
								break;
							}
							case 6:
							{
								$("#folderfail").html("Sorry, this domain is reserved, please try another one.");
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
