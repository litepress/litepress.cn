<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div style="max-width: 560px;padding: 20px;background: #ffffff;border-radius: 5px;margin:40px auto;font-family: Open Sans,Helvetica,Arial;font-size: 15px;color: #666;">

	<div style="color: #444444;font-weight: normal;">
		<div style="text-align: center;font-weight:600;font-size:26px;padding: 10px 0;border-bottom: solid 3px #eeeeee;">{site_name}</div>
		
		<div style="clear:both"></div>
	</div>
	
	<div style="padding: 0 30px 30px 30px;border-bottom: 3px solid #eeeeee;">

		<div style="padding: 30px 0;font-size: 24px;text-align: center;line-height: 40px;">Thank you for signing up!<span style="display: block;">Your account is now approved.</span></div>

		<div style="padding: 10px 0 50px 0;text-align: center;"><a href="{login_url}" style="background: #555555;color: #fff;padding: 12px 30px;text-decoration: none;border-radius: 3px;letter-spacing: 0.3px;">Login to our site</a></div>
		
		<div style="padding: 0 0 15px 0;">
		
			<div style="background: #eee;color: #444;padding: 12px 15px; border-radius: 3px;font-weight: bold;font-size: 16px;">Account Information</div>
		
			<div style="padding: 10px 15px 0 15px;color: #333;"><span style="color:#999">Your account e-mail:</span> <span style="font-weight:bold">{email}</span></div>
			<div style="padding: 10px 15px 0 15px;color: #333;"><span style="color:#999">Your account username:</span> <span style="font-weight:bold">{username}</span></div>
			<div style="padding: 10px 15px 0 15px;color: #333;"><span style="color:#999">Set your password:</span> <span style="font-weight:bold"><a href="{password_reset_link}" style="color: #3ba1da;text-decoration: none;">{password_reset_link}</a></span></div>
		
		</div>
		
	</div>
	
	<div style="color: #999;padding: 20px 30px">
		
		<div style="">Thank you!</div>
		<div style="">The <a href="{site_url}" style="color: #3ba1da;text-decoration: none;">{site_name}</a> Team</div>
		
	</div>

</div>