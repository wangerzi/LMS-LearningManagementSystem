/*!
 * jQuery.picboxed插件，用于放大图片，适用于各种需要对图片进行全屏放大操作的地方。
 * 
 * Author:Jeffrey Wang
 *
 * Version:v1.1.4
 *
 * Usage: <img class="picboxed" src="..." data-header="头部信息" data-footer="尾部信息" >，动态添加图片时需要自行初始化：$(...).picboxed();
 *
 * Info: 灵感来源materialze的materiabox插件，但在实际部署中遇到了一些问题，于是就重写了，不依赖除jQuery以外的其他库。
 */
(function($){
	$.fn.picboxed=function(){
		return this.each(function(){
			origin = $(this);

			if(origin.is('.initialized')){
				return ;
			}
			origin.addClass('initialized');//添加初始化标记，避免重复初始化。
			origin.on('click',function(){
				origin = $(this);
				screenWidth = window.innerWidth;
				screenHeight = window.innerHeight;

				item = origin.parents('.carousel-item:first');
				//console.log(item.is('.active'));
				if(item.length && !item.is('.active'))//跟materialze的carousel融合时出现的问题，如果是item并且不是激活状态则不进行操作。
					return ;

				oldWidth = origin.width();//在图片加载完成后执行，否则获取高度为0。
				oldHeight = origin.height();
				//console.log(oldWidth+'+'+oldHeight);

				newWidth = screenWidth * 0.8;
				newHeight = newWidth * oldHeight / oldWidth;
				//console.log(newWidth+':'+newHeight);
				if(newHeight > screenHeight){
					newHeight = screenHeight*0.8;
					newWidth = newHeight * (oldWidth / oldHeight);
				}
				header = origin.attr('data-header');
				footer = origin.attr('data-footer');
				header = typeof header == 'undefined' ? '' : header;
				footer = typeof footer == 'undefined' ? '' : footer;

				wrap = '<div class="img-wrap"></a>';
				override = $('<div></div>').addClass('picboxed-override').css({
					width:screenWidth,
					height:screenHeight,
					//opacity:0,								#背景淡入总感觉不和谐。
				})
					.append('<div class="img-header">'+header+'</div>')
					//.animate({opacity:1},'normal')
					.append(origin.clone().css({
						width:oldWidth,
						height:oldHeight,
						top:origin.offset().top-$(document).scrollTop(),
						left:origin.offset().left
					}))//放置到展区中，设置css是为了过渡平缓，从原图位置开始动画。
					.append('<div class="img-footer">'+footer+'</div>')
					.click(function(){
						returnToOrigin();
					});

				clone = $("body").append(override).find('img.picboxed:last');

				clone
					.wrap(wrap)
					.addClass('active')
					.animate({
						width : newWidth,
						height: newHeight,
						top:(screenHeight - newHeight)/2,
						left:(screenWidth - newWidth)/2,
					},'slow');

				var override = $("body .picboxed-override:last");
				//滚动即放大或缩小。
				if(document.addEventListener){ document.addEventListener('DOMMouseScroll',scrollFunc,false);}
				window.onmousewheel=document.onmousewheel=scrollFunc;//IE/Opera/Chrome
			});
			//滚动即放大或缩小。
			var scrollFunc = function(e){
				e.preventDefault();//这句话加上之后，会导致缩小后滚动条不可用。

				//正代表上，负代表下。
				if(e.wheelDelta){//IE/Opera/Chrome
					var between=-e.wheelDelta;
				}else if(e.detail){//Firefox
					var between=-e.detail*40;
				}

				//alert(between);

				//重新获取高度和宽度
				var nowWidth = clone.width();
				var nowHeight = clone.height();
				var nowTop = parseInt(clone.css('top'));
				var nowLeft = parseInt(clone.css('left'));


				//between>0代表缩小,between<0代表放大。
				var slideWidth = nowWidth-2*between;
				var slideHeight = nowHeight*slideWidth/nowWidth;//按照比例算出

				//鼠标位置
				var screenX = e.screenX;
				var screenY = e.screenY;
				//console.log(screenX+':'+screenY+'and'+nowLeft+':'+nowTop);

				//只能放大图片中的某一块，不能放大阴影部分。
				//是否在左侧或上侧
				screenX = screenX<nowLeft?nowLeft:screenX;
				screenY = screenY<nowTop?nowTop:screenY;

				//是否在右侧和下侧，避免出界。
				screenX = screenX>nowWidth+nowLeft?nowWidth+nowLeft:screenX;
				screenY = screenY>nowHeight+nowTop?nowHeight+nowTop:screenY;

				//console.log(screenX+':'+screenY);

				//居中偏移点加上鼠标的位置到重点的距离，只在放大的时候使用。

				slideTop = (screenHeight - slideHeight) / 2 + screenHeight/2 - screenY;
				slideLeft = (screenWidth - slideWidth) / 2 + screenWidth/2 - screenX;


				//防止过大或过小。
				if(slideWidth>screenWidth+400 && slideHeight>screenHeight+400 || slideWidth<100 && slideHeight<100)
					return ;

				clone.stop(true,false).animate({
					width	: slideWidth,
					height	: slideHeight,
					top		: slideTop,
					left	: slideLeft,
				},'fast');
				//returnToOrigin();
			};

			//返回页面
			function returnToOrigin(){
				override = $("body .picboxed-override:last");
				clone = override.find('img.picboxed');

				if(document.removeEventListener){document.removeEventListener('DOMMouseScroll',scrollFunc,false);}
				window.onmousewheel=document.onmousewheel=function(){};

				//返回的正常Top
				var backTop = origin.offset().top-$(document).scrollTop();
				var backLeft = origin.offset().left;

				console.log(backTop+':'+backLeft);

				//某些元素被隐藏，无法获取到实时的位置，获取到的是相对页面(0,0)，所以就居中缩小。
				backTop = backTop==-$(document).scrollTop()?(screenHeight - oldHeight)/2:backTop;
				backLeft = backLeft==0?(screenWidth - oldWidth)/2:backLeft;

				//override.fadeOut('normal');				#背景淡出总感觉不和谐。
				clone.animate({
					width:oldWidth,
					height:oldHeight,
					//top:(screenHeight - oldHeight)/2,
					//left:(screenWidth - oldWidth)/2,
					top:backTop,//恢复到图片所在的绝对位置，或者居中。
					left:backLeft,
				},'normal',function(){
					//console.log((origin.offset().top-$(document).scrollTop())+':'+origin.offset().left);
					override.remove();
					origin.removeClass('active');
				});
			}
		});
	}
	$(document).ready(function(){
		$('.picboxed').picboxed();
	});
} ( jQuery ) );