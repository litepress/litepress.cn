<?php
/**
 * Plugin Name: 维护模式
 * Description: 通过配置wp-config.php中的MAINTENANCE_MODE常量来开启全站维护模式
 * Author: LitePress团队
 * Author URI:https://litepress.cn/
 * Version: 1.0.0
 * Network: True
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( defined( 'MAINTENANCE_MODE' ) && MAINTENANCE_MODE ) {
	add_action( 'init', 'maintenance_mode' );
}
function maintenance_mode(): void {
	echo <<<HTML
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en-US"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en-US"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en-US"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en-US"> <!--<![endif]-->
<head>
<title>Whoops: Server maintenance</title>
<meta charset="UTF-8" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
<meta name="robots" content="noindex, nofollow" />
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1" />
<style>
#cf-wrapper a,#cf-wrapper abbr,#cf-wrapper article,#cf-wrapper aside,#cf-wrapper b,#cf-wrapper big,#cf-wrapper blockquote,#cf-wrapper body,#cf-wrapper canvas,#cf-wrapper caption,#cf-wrapper center,#cf-wrapper cite,#cf-wrapper code,#cf-wrapper dd,#cf-wrapper del,#cf-wrapper details,#cf-wrapper dfn,#cf-wrapper div,#cf-wrapper dl,#cf-wrapper dt,#cf-wrapper em,#cf-wrapper embed,#cf-wrapper fieldset,#cf-wrapper figcaption,#cf-wrapper figure,#cf-wrapper footer,#cf-wrapper form,#cf-wrapper h1,#cf-wrapper h2,#cf-wrapper h3,#cf-wrapper h4,#cf-wrapper h5,#cf-wrapper h6,#cf-wrapper header,#cf-wrapper hgroup,#cf-wrapper html,#cf-wrapper i,#cf-wrapper iframe,#cf-wrapper img,#cf-wrapper label,#cf-wrapper legend,#cf-wrapper li,#cf-wrapper mark,#cf-wrapper menu,#cf-wrapper nav,#cf-wrapper object,#cf-wrapper ol,#cf-wrapper output,#cf-wrapper p,#cf-wrapper pre,#cf-wrapper s,#cf-wrapper samp,#cf-wrapper section,#cf-wrapper small,#cf-wrapper span,#cf-wrapper strike,#cf-wrapper strong,#cf-wrapper sub,#cf-wrapper summary,#cf-wrapper sup,#cf-wrapper table,#cf-wrapper tbody,#cf-wrapper td,#cf-wrapper tfoot,#cf-wrapper th,#cf-wrapper thead,#cf-wrapper tr,#cf-wrapper tt,#cf-wrapper u,#cf-wrapper ul{margin:0;padding:0;border:0;font:inherit;font-size:100%;text-decoration:none;vertical-align:baseline}#cf-wrapper a img{border:none}#cf-wrapper article,#cf-wrapper aside,#cf-wrapper details,#cf-wrapper figcaption,#cf-wrapper figure,#cf-wrapper footer,#cf-wrapper header,#cf-wrapper hgroup,#cf-wrapper menu,#cf-wrapper nav,#cf-wrapper section,#cf-wrapper summary{display:block}#cf-wrapper .cf-columns:after,#cf-wrapper .cf-columns:before,#cf-wrapper .cf-section:after,#cf-wrapper .cf-section:before,#cf-wrapper .cf-wrapper:after,#cf-wrapper .cf-wrapper:before,#cf-wrapper .clearfix:after,#cf-wrapper .clearfix:before,#cf-wrapper section:after,#cf-wrapper section:before{content:" ";display:table}#cf-wrapper .cf-columns:after,#cf-wrapper .cf-section:after,#cf-wrapper .cf-wrapper:after,#cf-wrapper .clearfix:after,#cf-wrapper section:after{clear:both}#cf-wrapper{display:block;margin:0;padding:0;position:relative;text-align:left;width:100%;z-index:999999999;color:#404040!important;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Helvetica Neue,Arial,sans-serif!important;font-size:15px!important;line-height:1.5!important;text-decoration:none!important;letter-spacing:normal;-webkit-tap-highlight-color:rgba(246,139,31,.3);-webkit-font-smoothing:antialiased}#cf-wrapper .cf-section,#cf-wrapper section{background:0 0;display:block;margin-bottom:2em;margin-top:2em}#cf-wrapper .cf-wrapper{margin-left:auto;margin-right:auto;width:90%}#cf-wrapper .cf-columns{display:block;list-style:none;padding:0;width:100%}#cf-wrapper .cf-columns img,#cf-wrapper .cf-columns input,#cf-wrapper .cf-columns object,#cf-wrapper .cf-columns select,#cf-wrapper .cf-columns textarea{max-width:100%}#cf-wrapper .cf-columns>.cf-column{float:left;padding-bottom:45px;width:100%;box-sizing:border-box}@media screen and (min-width:49.2em){#cf-wrapper .cf-columns.cols-2>.cf-column:nth-child(n+3),#cf-wrapper .cf-columns.cols-3>.cf-column:nth-child(n+4),#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(n+3),#cf-wrapper .cf-columns.four>.cf-column:nth-child(n+3),#cf-wrapper .cf-columns.three>.cf-column:nth-child(n+4),#cf-wrapper .cf-columns.two>.cf-column:nth-child(n+3){padding-top:67.5px}#cf-wrapper .cf-columns>.cf-column{padding-bottom:0}#cf-wrapper .cf-columns.cols-2>.cf-column,#cf-wrapper .cf-columns.cols-4>.cf-column,#cf-wrapper .cf-columns.four>.cf-column,#cf-wrapper .cf-columns.two>.cf-column{padding-left:0;padding-right:22.5px;width:50%}#cf-wrapper .cf-columns.cols-2>.cf-column:nth-child(2n),#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(2n),#cf-wrapper .cf-columns.four>.cf-column:nth-child(2n),#cf-wrapper .cf-columns.two>.cf-column:nth-child(2n){padding-left:22.5px;padding-right:0}#cf-wrapper .cf-columns.cols-2>.cf-column:nth-child(odd),#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(odd),#cf-wrapper .cf-columns.four>.cf-column:nth-child(odd),#cf-wrapper .cf-columns.two>.cf-column:nth-child(odd){clear:left}#cf-wrapper .cf-columns.cols-3>.cf-column,#cf-wrapper .cf-columns.three>.cf-column{padding-left:30px;width:33.3333333333333%}#cf-wrapper .cf-columns.cols-3>.cf-column:first-child,#cf-wrapper .cf-columns.cols-3>.cf-column:nth-child(3n+1),#cf-wrapper .cf-columns.three>.cf-column:first-child,#cf-wrapper .cf-columns.three>.cf-column:nth-child(3n+1){clear:left;padding-left:0;padding-right:30px}#cf-wrapper .cf-columns.cols-3>.cf-column:nth-child(3n+2),#cf-wrapper .cf-columns.three>.cf-column:nth-child(3n+2){padding-left:15px;padding-right:15px}#cf-wrapper .cf-columns.cols-3>.cf-column:nth-child(-n+3),#cf-wrapper .cf-columns.three>.cf-column:nth-child(-n+3){padding-top:0}}@media screen and (min-width:66em){#cf-wrapper .cf-columns>.cf-column{padding-bottom:0}#cf-wrapper .cf-columns.cols-4>.cf-column,#cf-wrapper .cf-columns.four>.cf-column{padding-left:33.75px;width:25%}#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(odd),#cf-wrapper .cf-columns.four>.cf-column:nth-child(odd){clear:none}#cf-wrapper .cf-columns.cols-4>.cf-column:first-child,#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(4n+1),#cf-wrapper .cf-columns.four>.cf-column:first-child,#cf-wrapper .cf-columns.four>.cf-column:nth-child(4n+1){clear:left;padding-left:0;padding-right:33.75px}#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(4n+2),#cf-wrapper .cf-columns.four>.cf-column:nth-child(4n+2){padding-left:11.25px;padding-right:22.5px}#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(4n+3),#cf-wrapper .cf-columns.four>.cf-column:nth-child(4n+3){padding-left:22.5px;padding-right:11.25px}#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(n+5),#cf-wrapper .cf-columns.four>.cf-column:nth-child(n+5){padding-top:67.5px}#cf-wrapper .cf-columns.cols-4>.cf-column:nth-child(-n+4),#cf-wrapper .cf-columns.four>.cf-column:nth-child(-n+4){padding-top:0}}#cf-wrapper a{background:0 0;border:0;color:#2f7bbf;outline:0;text-decoration:none;-webkit-transition:all .15s ease;transition:all .15s ease}#cf-wrapper a:hover{background:0 0;border:0;color:#f68b1f}#cf-wrapper a:focus{background:0 0;border:0;color:#62a1d8;outline:0}#cf-wrapper a:active{background:0 0;border:0;color:#c16508;outline:0}#cf-wrapper h1,#cf-wrapper h2,#cf-wrapper h3,#cf-wrapper h4,#cf-wrapper h5,#cf-wrapper h6,#cf-wrapper p{color:#404040;margin:0;padding:0}#cf-wrapper h1,#cf-wrapper h2,#cf-wrapper h3{font-weight:400}#cf-wrapper h4,#cf-wrapper h5,#cf-wrapper h6,#cf-wrapper strong{font-weight:600}#cf-wrapper h1{font-size:36px;line-height:1.2}#cf-wrapper h2{font-size:30px;line-height:1.3}#cf-wrapper h3{font-size:25px;line-height:1.3}#cf-wrapper h4{font-size:20px;line-height:1.3}#cf-wrapper h5{font-size:15px}#cf-wrapper h6{font-size:13px}#cf-wrapper ol,#cf-wrapper ul{list-style:none;margin-left:3em}#cf-wrapper ul{list-style-type:disc}#cf-wrapper ol{list-style-type:decimal}#cf-wrapper em{font-style:italic}#cf-wrapper .cf-subheadline{color:#999;font-weight:300}#cf-wrapper .cf-text-error{color:#bd2426}#cf-wrapper .cf-text-success{color:#9bca3e}#cf-wrapper ol+h2,#cf-wrapper ol+h3,#cf-wrapper ol+h4,#cf-wrapper ol+h5,#cf-wrapper ol+h6,#cf-wrapper ol+p,#cf-wrapper p+dl,#cf-wrapper p+ol,#cf-wrapper p+p,#cf-wrapper p+table,#cf-wrapper p+ul,#cf-wrapper ul+h2,#cf-wrapper ul+h3,#cf-wrapper ul+h4,#cf-wrapper ul+h5,#cf-wrapper ul+h6,#cf-wrapper ul+p{margin-top:1.5em}#cf-wrapper h1+p,#cf-wrapper p+h1,#cf-wrapper p+h2,#cf-wrapper p+h3,#cf-wrapper p+h4,#cf-wrapper p+h5,#cf-wrapper p+h6{margin-top:1.25em}#cf-wrapper h1+h2,#cf-wrapper h1+h3,#cf-wrapper h2+h3,#cf-wrapper h3+h4,#cf-wrapper h4+h5{margin-top:.25em}#cf-wrapper h2+p{margin-top:1em}#cf-wrapper h1+h4,#cf-wrapper h1+h5,#cf-wrapper h1+h6,#cf-wrapper h2+h4,#cf-wrapper h2+h5,#cf-wrapper h2+h6,#cf-wrapper h3+h5,#cf-wrapper h3+h6,#cf-wrapper h3+p,#cf-wrapper h4+p,#cf-wrapper h5+ol,#cf-wrapper h5+p,#cf-wrapper h5+ul{margin-top:.5em}#cf-wrapper .cf-btn{background-color:transparent;border:1px solid #999;color:#404040;font-size:14px;font-weight:400;line-height:1.2;margin:0;padding:.6em 1.33333em .53333em;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;display:-moz-inline-stack;display:inline-block;vertical-align:middle;zoom:1;border-radius:2px;box-sizing:border-box;-webkit-transition:all .2s ease;transition:all .2s ease}#cf-wrapper .cf-btn:hover{background-color:#bfbfbf;border:1px solid #737373;color:#fff}#cf-wrapper .cf-btn:focus{color:inherit;outline:0;box-shadow:inset 0 0 4px rgba(0,0,0,.3)}#cf-wrapper .cf-btn.active,#cf-wrapper .cf-btn:active{background-color:#bfbfbf;border:1px solid #404040;color:#272727}#cf-wrapper .cf-btn::-moz-focus-inner{padding:0;border:0}#cf-wrapper .cf-btn .cf-caret{border-top-color:currentColor;margin-left:.25em;margin-top:.18333em}#cf-wrapper .cf-btn-primary{background-color:#2f7bbf;border:1px solid transparent;color:#fff}#cf-wrapper .cf-btn-primary:hover{background-color:#62a1d8;border:1px solid #2f7bbf;color:#fff}#cf-wrapper .cf-btn-primary.active,#cf-wrapper .cf-btn-primary:active,#cf-wrapper .cf-btn-primary:focus{background-color:#62a1d8;border:1px solid #163959;color:#fff}#cf-wrapper .cf-btn-danger,#cf-wrapper .cf-btn-error,#cf-wrapper .cf-btn-important{background-color:#bd2426;border-color:transparent;color:#fff}#cf-wrapper .cf-btn-danger:hover,#cf-wrapper .cf-btn-error:hover,#cf-wrapper .cf-btn-important:hover{background-color:#de5052;border-color:#bd2426;color:#fff}#cf-wrapper .cf-btn-danger.active,#cf-wrapper .cf-btn-danger:active,#cf-wrapper .cf-btn-danger:focus,#cf-wrapper .cf-btn-error.active,#cf-wrapper .cf-btn-error:active,#cf-wrapper .cf-btn-error:focus,#cf-wrapper .cf-btn-important.active,#cf-wrapper .cf-btn-important:active,#cf-wrapper .cf-btn-important:focus{background-color:#de5052;border-color:#521010;color:#fff}#cf-wrapper .cf-btn-accept,#cf-wrapper .cf-btn-success{background-color:#9bca3e;border:1px solid transparent;color:#fff}#cf-wrapper .cf-btn-accept:hover,#cf-wrapper .cf-btn-success:hover{background-color:#bada7a;border:1px solid #9bca3e;color:#fff}#cf-wrapper .active.cf-btn-accept,#cf-wrapper .cf-btn-accept:active,#cf-wrapper .cf-btn-accept:focus,#cf-wrapper .cf-btn-success.active,#cf-wrapper .cf-btn-success:active,#cf-wrapper .cf-btn-success:focus{background-color:#bada7a;border:1px solid #516b1d;color:#fff}#cf-wrapper .cf-btn-accept{color:transparent;font-size:0;height:36.38px;overflow:hidden;position:relative;text-indent:0;width:36.38px;white-space:nowrap}#cf-wrapper input,#cf-wrapper select,#cf-wrapper textarea{background:#fff!important;border:1px solid #999!important;color:#404040!important;font-size:.86667em!important;line-height:1.24!important;margin:0 0 1em!important;max-width:100%!important;outline:0!important;padding:.45em .75em!important;vertical-align:middle!important;display:-moz-inline-stack;display:inline-block;zoom:1;box-sizing:border-box;-webkit-transition:all .2s ease;transition:all .2s ease;border-radius:2px}#cf-wrapper input:hover,#cf-wrapper select:hover,#cf-wrapper textarea:hover{border-color:gray}#cf-wrapper input:focus,#cf-wrapper select:focus,#cf-wrapper textarea:focus{border-color:#2f7bbf;outline:0;box-shadow:0 0 8px rgba(47,123,191,.5)}#cf-wrapper fieldset{width:100%}#cf-wrapper label{display:block;font-size:13px;margin-bottom:.38333em}#cf-wrapper .cf-form-stacked .select2-container,#cf-wrapper .cf-form-stacked input,#cf-wrapper .cf-form-stacked select,#cf-wrapper .cf-form-stacked textarea{display:block;width:100%}#cf-wrapper .cf-form-stacked input[type=button],#cf-wrapper .cf-form-stacked input[type=checkbox],#cf-wrapper .cf-form-stacked input[type=submit]{display:-moz-inline-stack;display:inline-block;vertical-align:middle;zoom:1;width:auto}#cf-wrapper .cf-form-actions{text-align:right}#cf-wrapper .cf-alert{background-color:#f9b169;border:1px solid #904b06;color:#404040;font-size:13px;padding:7.5px 15px;position:relative;vertical-align:middle;border-radius:2px}#cf-wrapper .cf-alert:empty{display:none}#cf-wrapper .cf-alert .cf-close{border:1px solid transparent;color:inherit;font-size:18.75px;line-height:1;padding:0;position:relative;right:-18.75px;top:0}#cf-wrapper .cf-alert .cf-close:hover{background-color:transparent;border-color:currentColor;color:inherit}#cf-wrapper .cf-alert-danger,#cf-wrapper .cf-alert-error{background-color:#de5052;border-color:#521010;color:#fff}#cf-wrapper .cf-alert-success{background-color:#bada7a;border-color:#516b1d;color:#516b1d}#cf-wrapper .cf-alert-warning{background-color:#f9b169;border-color:#904b06;color:#404040}#cf-wrapper .cf-alert-info{background-color:#62a1d8;border-color:#163959;color:#163959}#cf-wrapper .cf-alert-nonessential{background-color:#ebebeb;border-color:#999;color:#404040}#cf-wrapper .cf-icon-exclamation-sign{background:url(https://cloudflare.com/cdn-cgi/images/icon-exclamation.png?1376755637) 50% no-repeat;height:54px;width:54px;display:-moz-inline-stack;display:inline-block;vertical-align:middle;zoom:1}#cf-wrapper h1 .cf-icon-exclamation-sign{margin-top:-10px}#cf-wrapper #cf-error-banner{background-color:#fff;border-bottom:3px solid #f68b1f;padding:15px 15px 20px;position:relative;z-index:999999999;box-shadow:0 2px 8px rgba(0,0,0,.2)}#cf-wrapper #cf-error-banner h4,#cf-wrapper #cf-error-banner p{display:-moz-inline-stack;display:inline-block;vertical-align:bottom;zoom:1}#cf-wrapper #cf-error-banner h4{color:#2f7bbf;font-weight:400;font-size:20px;line-height:1;vertical-align:baseline}#cf-wrapper #cf-error-banner .cf-error-actions{margin-bottom:10px;text-align:center;width:100%}#cf-wrapper #cf-error-banner .cf-error-actions a{display:-moz-inline-stack;display:inline-block;vertical-align:middle;zoom:1}#cf-wrapper #cf-error-banner .cf-error-actions a+a{margin-left:10px}#cf-wrapper #cf-error-banner .cf-error-actions .cf-btn-accept,#cf-wrapper #cf-error-banner .cf-error-actions .cf-btn-success{color:#fff}#cf-wrapper #cf-error-banner .error-header-desc{text-align:left}#cf-wrapper #cf-error-banner .cf-close{color:#999;cursor:pointer;display:inline-block;font-size:34.5px;float:none;font-weight:700;height:22.5px;line-height:.6;overflow:hidden;position:absolute;right:20px;top:25px;text-indent:200%;width:22.5px}#cf-wrapper #cf-error-banner .cf-close:hover{color:gray}#cf-wrapper #cf-error-banner .cf-close:before{content:"\00D7";left:0;height:100%;position:absolute;text-align:center;text-indent:0;top:0;width:100%}#cf-inline-error-wrapper{box-shadow:0 2px 10px rgba(0,0,0,.5)}#cf-wrapper #cf-error-details{background:#fff}#cf-wrapper #cf-error-details .cf-error-overview{padding:25px 0 0}#cf-wrapper #cf-error-details .cf-error-overview h1,#cf-wrapper #cf-error-details .cf-error-overview h2{font-weight:300}#cf-wrapper #cf-error-details .cf-error-overview h2{margin-top:0}#cf-wrapper #cf-error-details .cf-highlight{background:#ebebeb;overflow-x:hidden;padding:30px 0;background-image:-webkit-gradient(linear,left top, left bottom,from(#dedede),color-stop(3%, #ebebeb),color-stop(97%, #ebebeb),to(#dedede));background-image:linear-gradient(top,#dedede,#ebebeb 3%,#ebebeb 97%,#dedede)}#cf-wrapper #cf-error-details .cf-highlight h3{color:#999;font-weight:300}#cf-wrapper #cf-error-details .cf-highlight .cf-column:last-child{padding-bottom:0}#cf-wrapper #cf-error-details .cf-highlight .cf-highlight-inverse{background-color:#fff;padding:15px;border-radius:2px}#cf-wrapper #cf-error-details .cf-status-display h3{margin-top:.5em}#cf-wrapper #cf-error-details .cf-status-label{color:#9bca3e;font-size:1.46667em}#cf-wrapper #cf-error-details .cf-status-label,#cf-wrapper #cf-error-details .cf-status-name{display:inline}#cf-wrapper #cf-error-details .cf-status-item{display:block;position:relative;text-align:left}#cf-wrapper #cf-error-details .cf-status-item,#cf-wrapper #cf-error-details .cf-status-item.cf-column{padding-bottom:1.5em}#cf-wrapper #cf-error-details .cf-status-item.cf-error-source{display:block;text-align:center}#cf-wrapper #cf-error-details .cf-status-item.cf-error-source:after{bottom:-60px;content:"";display:none;border-bottom:18px solid #fff;border-left:20px solid transparent;border-right:20px solid transparent;height:0;left:50%;margin-left:-9px;position:absolute;right:50%;width:0}#cf-wrapper #cf-error-details .cf-status-item+.cf-status-item{border-top:1px solid #dedede;padding-top:1.5em}#cf-wrapper #cf-error-details .cf-status-item+.cf-status-item:before{background:url(https://img-cdn.haozi.xyz/2021/08/24/440318bf5f8c7ecbe69fe9c4cf3fbd9e.png) no-repeat;content:"";display:block;left:0;position:absolute;top:25.67px}#cf-wrapper #cf-error-details .cf-error-source .cf-icon-error-container{height:85px;margin-bottom:2.5em}#cf-wrapper #cf-error-details .cf-error-source .cf-status-label{color:#bd2426}#cf-wrapper #cf-error-details .cf-error-source .cf-icon{display:block}#cf-wrapper #cf-error-details .cf-error-source .cf-icon-status{bottom:-10px;left:50%;top:auto;right:auto}#cf-wrapper #cf-error-details .cf-error-source .cf-status-label,#cf-wrapper #cf-error-details .cf-error-source .cf-status-name{display:block}#cf-wrapper #cf-error-details .cf-icon-error-container{height:auto;position:relative}#cf-wrapper #cf-error-details .cf-icon-status{display:block;margin-left:-24px;position:absolute;top:0;right:0}#cf-wrapper #cf-error-details .cf-icon{display:none;margin:0 auto}#cf-wrapper #cf-error-details .cf-status-desc{display:block;height:22.5px;overflow:hidden;text-overflow:ellipsis;width:100%;white-space:nowrap}#cf-wrapper #cf-error-details .cf-status-desc:empty{display:none}#cf-wrapper #cf-error-details .cf-error-footer{padding:1.33333em 0;border-top:1px solid #ebebeb;text-align:center}#cf-wrapper #cf-error-details .cf-error-footer p{font-size:13px}#cf-wrapper #cf-error-details .cf-error-footer select{margin:0!important}#cf-wrapper #cf-error-details .cf-footer-item{display:block;margin-bottom:5px;text-align:left}#cf-wrapper #cf-error-details .cf-footer-separator{display:none}#cf-wrapper #cf-error-details .cf-captcha-info{margin-bottom:10px;position:relative;text-align:center}#cf-wrapper #cf-error-details .cf-captcha-image{height:57px;width:300px}#cf-wrapper #cf-error-details .cf-captcha-actions{margin-top:15px}#cf-wrapper #cf-error-details .cf-captcha-actions a{font-size:0;height:36.38px;overflow:hidden;padding-left:1.2em;padding-right:1.2em;position:relative;text-indent:-9999px;width:36.38px;white-space:nowrap}#cf-wrapper #cf-error-details .cf-captcha-actions a.cf-icon-refresh span{background-position:0 -787px}#cf-wrapper #cf-error-details .cf-captcha-actions a.cf-icon-announce span{background-position:0 -767px}#cf-wrapper #cf-error-details .cf-captcha-actions a.cf-icon-question span{background-position:0 -827px}#cf-wrapper #cf-error-details .cf-screenshot-container{background:url(https://cloudflare.com/cdn-cgi/images/browser-bar.png?1376755637) no-repeat #fff;max-height:400px;max-width:100%;overflow:hidden;padding-top:53px;width:960px;border-radius:5px 5px 0 0}#cf-wrapper #cf-error-details .cf-screenshot-container .cf-no-screenshot{background:url(https://cloudflare.com/cdn-cgi/images/cf-no-screenshot-warn.png) no-repeat;display:block;height:158px;left:25%;margin-top:-79px;overflow:hidden;position:relative;top:50%;width:178px}#cf-wrapper #cf-error-details .cf-captcha-container .cf-screenshot-container,#cf-wrapper #cf-error-details .cf-captcha-container .cf-screenshot-container img,#recaptcha-widget .cf-alert,#recaptcha-widget .recaptcha_only_if_audio,.cf-cookie-error{display:none}#cf-wrapper #cf-error-details .cf-screenshot-container .cf-no-screenshot.error{background:url(https://cloudflare.com/cdn-cgi/images/cf-no-screenshot-error.png) no-repeat;height:175px}#cf-wrapper #cf-error-details .cf-screenshot-container.cf-screenshot-full .cf-no-screenshot{left:50%;margin-left:-89px}.cf-captcha-info iframe{max-width:100%}#cf-wrapper .cf-icon-ok{background:url(https://img-cdn.haozi.xyz/2021/08/24/8e61bfcef8671720a15a607f0be851f7.png) no-repeat;height:48px;width:48px}#cf-wrapper .cf-icon-error{background:url(https://img-cdn.haozi.xyz/2021/08/24/d4e627c3f06ec6490bede5c30d7a94f4.png) no-repeat;height:48px;width:48px}#cf-wrapper .cf-icon-browser{background:url(https://img-cdn.haozi.xyz/2021/08/24/5cb76ecb067df2fdbd97fe4c5c578b6c.png) no-repeat;height:80px;width:100px}#cf-wrapper .cf-icon-cloud{background:url(https://img-cdn.haozi.xyz/2021/08/24/d436368578f91e0adf4037d87329fecd.png) no-repeat;height:77px;width:151px}#cf-wrapper .cf-icon-server{background:url(https://img-cdn.haozi.xyz/2021/08/24/749f4cfa7a5f15876681209737be0e0c.png) no-repeat;height:75px;width:95px}#cf-wrapper .cf-icon-railgun{background-position:0 -848px;height:81px;width:95px}#cf-wrapper .cf-caret{border:.33333em solid transparent;border-top-color:inherit;content:"";height:0;width:0;display:-moz-inline-stack;display:inline-block;vertical-align:middle;zoom:1}@media screen and (min-width:49.2em){#cf-wrapper #cf-error-details .cf-status-desc:empty,#cf-wrapper #cf-error-details .cf-status-item.cf-error-source:after,#cf-wrapper #cf-error-details .cf-status-item .cf-icon,#cf-wrapper #cf-error-details .cf-status-label,#cf-wrapper #cf-error-details .cf-status-name{display:block}#cf-wrapper .cf-wrapper{width:708px}#cf-wrapper #cf-error-banner{padding:20px 20px 25px}#cf-wrapper #cf-error-banner .cf-error-actions{margin-bottom:15px}#cf-wrapper #cf-error-banner .cf-error-header-desc h4{margin-right:.5em}#cf-wrapper #cf-error-details h1{font-size:4em}#cf-wrapper #cf-error-details .cf-error-overview{padding-top:2.33333em}#cf-wrapper #cf-error-details .cf-highlight{padding:4em 0}#cf-wrapper #cf-error-details .cf-status-item{text-align:center}#cf-wrapper #cf-error-details .cf-status-item,#cf-wrapper #cf-error-details .cf-status-item.cf-column{padding-bottom:0}#cf-wrapper #cf-error-details .cf-status-item+.cf-status-item{border:0;padding-top:0}#cf-wrapper #cf-error-details .cf-status-item+.cf-status-item:before{background-position:0 -544px;height:24.75px;margin-left:-37.5px;width:75px;background-size:131.25px auto}#cf-wrapper #cf-error-details .cf-icon-error-container{height:85px;margin-bottom:2.5em}#cf-wrapper #cf-error-details .cf-icon-status{bottom:-10px;left:50%;top:auto;right:auto}#cf-wrapper #cf-error-details .cf-error-footer{padding:2.66667em 0}#cf-wrapper #cf-error-details .cf-footer-item,#cf-wrapper #cf-error-details .cf-footer-separator{display:-moz-inline-stack;display:inline-block;vertical-align:baseline;zoom:1}#cf-wrapper #cf-error-details .cf-footer-separator{padding:0 .25em}#cf-wrapper #cf-error-details .cf-status-item.cloudflare-status:before{margin-left:-50px}#cf-wrapper #cf-error-details .cf-status-item.cloudflare-status+.status-item:before{margin-left:-25px}#cf-wrapper #cf-error-details .cf-screenshot-container{height:400px;margin-bottom:-4em;max-width:none}#cf-wrapper #cf-error-details .cf-captcha-container .cf-screenshot-container,#cf-wrapper #cf-error-details .cf-captcha-container .cf-screenshot-container img{display:block}}@media screen and (min-width:66em){#cf-wrapper .cf-wrapper{width:960px}#cf-wrapper #cf-error-banner .cf-close{position:relative;right:auto;top:auto}#cf-wrapper #cf-error-banner .cf-details{white-space:nowrap}#cf-wrapper #cf-error-banner .cf-details-link{padding-right:.5em}#cf-wrapper #cf-error-banner .cf-error-actions{float:right;margin-bottom:0;text-align:left;width:auto}#cf-wrapper #cf-error-details .cf-status-item+.cf-status-item:before{background-position:0 -734px;height:33px;margin-left:-50px;width:100px;background-size:auto}#cf-wrapper #cf-error-details .cf-status-item.cf-cloudflare-status:before{margin-left:-66.67px}#cf-wrapper #cf-error-details .cf-status-item.cf-cloudflare-status+.cf-status-item:before{margin-left:-37.5px}#cf-wrapper #cf-error-details .cf-captcha-image{float:left}#cf-wrapper #cf-error-details .cf-captcha-actions{position:absolute;top:0;right:0}}.no-js #cf-wrapper .js-only{display:none}#cf-wrapper #cf-error-details .heading-ray-id{font-family:monaco,courier,monospace;font-size:15px;white-space:nowrap}
</style>
<style type="text/css">body{margin:0;padding:0}</style>
</head>
<body>
<div id="cf-wrapper">
    <div id="cf-error-details" class="cf-error-details-wrapper">
        <div class="cf-wrapper cf-error-overview">
            <h1>
              
              <span class="cf-error-type">Whoops: </span>
              <span class="cf-error-code">Server down</span>
              <small class="heading-ray-id"></small>
            </h1>
            <h2 class="cf-subheadline">服务器维护中</h2>
        </div><!-- /.error-overview -->
        
        <div class="cf-section cf-highlight cf-status-display">
            <div class="cf-wrapper">
                <div class="cf-columns cols-3">
                  
<div id="cf-browser-status" class="cf-column cf-status-item cf-browser-status ">
  <div class="cf-icon-error-container">
    <i class="cf-icon cf-icon-browser"></i>
    <i class="cf-icon-status cf-icon-ok"></i>
  </div>
  <span class="cf-status-desc">您的</span>
  <h3 class="cf-status-name">浏览器</h3>
  <span class="cf-status-label">正常工作</span>
</div>

<div id="cf-cloudflare-status" class="cf-column cf-status-item cf-cloudflare-status ">
  <div class="cf-icon-error-container">
    <i class="cf-icon cf-icon-cloud"></i>
    <i class="cf-icon-status cf-icon-ok"></i>
  </div>
  <span class="cf-status-desc">稳坚盾</span>
  <h3 class="cf-status-name">CDN节点</h3>
  <span class="cf-status-label">正常工作</span>
</div>

<div id="cf-host-status" class="cf-column cf-status-item cf-host-status cf-error-source">
  <div class="cf-icon-error-container">
    <i class="cf-icon cf-icon-server"></i>
    <i class="cf-icon-status cf-icon-error"></i>
  </div>
  <span class="cf-status-desc">源站</span>
  <h3 class="cf-status-name">服务器</h3>
  <span class="cf-status-label">维护</span>
</div>
                </div>
              
            </div>
        </div><!-- /.status-display -->

        <div class="cf-section cf-wrapper">
            <div class="cf-columns two">
                <div class="cf-column">
                    <h2>发生了什么？</h2>
                      <p>我们的服务器已经驾鹤西去。</p>
                      <p>或许是因为遭到了 CC / DDOS ，</p>
                      <p>也可能是配置出错、例行维护。</p>
                </div>
              
                <div class="cf-column">
                    <h2>我能做些什么？</h2>
                      <p>1.尝试按下 F5 / Ctrl+R 刷新页面</p>
                      <p>2.若仍为此页面，请等待我们修复。</p>
                </div>
            </div>
              
        </div><!-- /.section -->

        <div class="cf-error-footer cf-wrapper">
  <p>
    <span class="cf-footer-item">错误：<strong>Server maintenance</strong></span>
    <span class="cf-footer-separator">&bull;</span>
    <span class="cf-footer-item"><span>时间：</span><strong><script>document.write(new Date().toLocaleString());</script></strong></span>
    <span class="cf-footer-separator">&bull;</span>
    <span class="cf-footer-item"><span>Powered By</span> <strong><a href="https://litepress.cn/" id="brand_link" target="_blank">LitePress社区</a></strong></span>
    
  </p>
</div><!-- /.error-footer -->
</div><!-- /#cf-error-details -->
</div><!-- /#cf-wrapper -->
</body>
</html>
HTML;
	exit;
}
