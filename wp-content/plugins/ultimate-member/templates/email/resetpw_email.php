<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div style="max-width: 560px;padding: 20px;background: #ffffff;border-radius: 5px;margin:40px auto;font-family: Open Sans,Helvetica,Arial;font-size: 15px;color: #666;">

	<div style="color: #444444;font-weight: normal;">
		<div style="text-align: center;font-weight:600;font-size:26px;padding: 10px 0;border-bottom: solid 3px #eeeeee;">{site_name}</div>
		
		<div style="clear:both"></div>
	</div>
	
	<div style="padding: 0 30px 30px 30px;border-bottom: 3px solid #eeeeee;">

		<div style="padding: 30px 0;font-size: 24px;text-align: center;line-height: 40px;">We received a request to reset the password for your account. If you made this request, click the link below to change your password:</div>

		<div style="padding: 10px 0 50px 0;text-align: center;"><a href="{password_reset_link}" style="background: #555555;color: #fff;padding: 12px 30px;text-decoration: none;border-radius: 3px;letter-spacing: 0.3px;">Reset your password</a></div>
		
		<div style="padding: 15px;background: #eee;border-radius: 3px;text-align: center;">If you didn't make this request, ignore this email or <a href="mailto:{admin_email}" style="color: #3ba1da;text-decoration: none;">report it to us</a>.</div>
		
	</div>
	
	<div style="color: #999;padding: 20px 30px">
		
		<div style="">Thank you!</div>
		<div style="">The <a href="{site_url}" style="color: #3ba1da;text-decoration: none;">{site_name}</a> Team</div>
		
	</div>

</div>