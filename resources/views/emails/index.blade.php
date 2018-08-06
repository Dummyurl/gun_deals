<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
    <body style="background: #eaeaea;">
        <div style="margin: 0; padding: 20px; background: #eaeaea;">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td>&nbsp;</td>
                    <td style="width: 700px;">	
                        <table width="100%" cellpadding="0" cellspacing="0" style="border: solid 1px #a6a5a5;-webkit-box-shadow: #aaa 1px 1px 3px; -moz-box-shadow: #aaa 1px 1px 3px; box-shadow: #aaa 1px 1px 3px;">
                            <tr>
                                <td colspan="3" style="background: #ffffff; height: 5px; border-top: solid 5px #cc534b;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="width: 37px; background: #ffffff;">&nbsp;</td>
                                <td style="vertical-align: top; background: #ffffff;">
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td style="height: 45px; padding: 0 20px; background: #A8A8AA; border-bottom: solid 1px #dcdcdc; border-top: solid 1px #dcdcdc; font-family: Tahoma, Arial, sans-serif; font-size: 26px; color: #5998cb;">
                                                <img src="{{ asset('images/logo.png') }}" style="width: 250px;"/>                        
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top" style="font-family: Arial, sans-serif; font-size: 12px; color: #464646; line-height: 18px; background: #ffffff; padding-top:10px;">

                                                <div style="clear: both">&nbsp;</div>
                                                <div style="border: 2px solid gray;padding-left: 15px;padding-right: 15px;">                                                    
                                                    {!! $body !!}
                                                    <div style="clear: both">&nbsp;</div>
                                                </div>
                                                <div style="clear: both">&nbsp;</div>
                                                <div style="clear: both">&nbsp;</div>
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family: Arial, sans-serif; font-size: 12px; color: #464646; line-height: 18px; background: #ffffff;">                        
                                                    <tr><td valign="top" style="font-size: 16px; color: #5998cb;">- The {{ env('APP_SITE_TITLE') }} Team</td></tr>
                                                </table>                      
                                            </td>
                                        </tr>

                                        <tr>
                                            <td valign="top" style="font-family: Arial, sans-serif; font-size: 12px; color: #464646; line-height: 18px; background: #ffffff; padding-top:10px;"><!-- Insert email content here -->
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-family: Arial, sans-serif; font-size: 12px; color: #464646; line-height: 18px; background: #ffffff;">


                                                </table></td>
                                        </tr>
                                    </table></td>
                                <td style="width: 37px; background: #ffffff;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="background: #ffffff; height: 45px;">&nbsp;</td>
                            </tr>
                        </table>
                        <table width="100%" cellpadding="10" cellspacing="0">
                            <tr>
                                <td style="text-align: center; font-family: Arial, sans-serif; font-size: 12px; line-height: 18px; color: #636466;"><strong style="font-size: 10px;">
                                        Copyright &copy; 2016 <a href="{{ url('/') }}" style="color: #004e78;">{{ env('APP_SITE_TITLE') }}.</a> All rights reserved.</strong><br/>
                                </td>
                            </tr>
                        </table>

                    </td>      
                    <td>&nbsp;</td>
                </tr>
            </table>    
        </div>
    </body>
</html>