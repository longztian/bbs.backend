<!DOCTYPE html>
<html lang='zh' dir='ltr'>
  <head>
    <meta charset='UTF-8' />
    <meta name='description' content='<?php echo $head_description; ?>' />

    <?php include $tpl_path . '/head_js.tpl.php'; ?>
    <?php include $tpl_path . '/head_css.tpl.php'; ?>

    <title><?php echo $head_title; ?></title>
    <link rel='apple-touch-icon' href='/apple-touch-icon.png' />
    <link rel='apple-touch-icon' sizes='72x72' href='/apple-touch-icon-72x72.png' />
    <link rel='apple-touch-icon' sizes='114x114' href='/apple-touch-icon-114x114.png' />
  </head>
  <body>
    <div id='page_overlay'><div id='popup'></div><div id='popup_bg'></div></div>
    <div id='page_header'>
      <div id='page_header_inner'>
        <div data-umode='<?php echo $umode_pc; ?>' style='position:relative; height:150px'>
          <div id='topmenu'>
            <form action='http://groups.google.com/group/houstonbbs/boxsubscribe'>
              <input type='hidden' name='hl' value='en'>
              <ul>
                <li>Email: <input type='text' name='email' size='15'></li>
                <li><input type='submit' name='sub' value='Join'> our <a href='http://groups.google.com/group/houstonbbs?hl=en' target='_blank'>Mailing List</a></li>
              </ul>
            </form>
          </div>

          <div id='logo-title'>
            <div id='logo'><a style='padding: 0pt; margin: 0pt; display: block; width: 60px; height: 60px;' href='/' title='首页' rel='home'><img src='/themes/default/images/logo_60x60.png' alt='首页' id='logo-image'></a></div>
            <div id='site-name'><span style='color: #A0522D;'>缤纷休斯顿</span></div>
            <div id='site-slogan'><span style='color: #32CD32;'>We share</span><span style='color: #A0522D;'> - </span><span style='color: #1E90FF;'>We care</span><span style='color: #A0522D;'> - </span><span style='color: #B22222;'>We inspire</span></div>
          </div>
        </div>
        <?php echo $page_navbar; ?>
        <div style="clear:both;"></div>
      </div>
    </div>
    <div id='page_body'>
      <div id='page_body_inner'>
        <?php echo $content; ?>
      </div>
      <div style="clear:both;"></div>
    </div>
    <div id='page_navbar_mobile' data-umode='<?php echo $umode_mobile; ?>'><div>页面结束，以下为站内快捷链接：</div></div>
    <div id='page_footer'>
      <div id='page_footer_inner'>
        <div id='copyright'>Contact the Web Administrator at
          <span class='highlight'>admin@houstonbbs.com</span> | Copyright © 2009-2013 HoustonBBS.com. All rights reserved. | <a href='/term'>Terms and Conditions</a>
        </div>
      </div>
      <div style="clear:both;"></div>
    </div>
    <div id='page_data' style='display:none;'><?php echo $page_data; ?></div>
  </body>
</html>
