<!DOCTYPE html>
<html lang='zh' dir='ltr'>
   <head>
      <meta charset='UTF-8' />
      <meta name='description' content='<?php print $head_description; ?>' />      
      <meta name='viewport' content='width=device-width, initial-scale=1' />

      <!--BEBIN JS-->
      <script>
         if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
            document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"><\/script>');
         } else {
            document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"><\/script>');
         }
      </script>
      <script>
         if (!window.jQuery)
         {
            if ('querySelector' in document && 'localStorage' in window && 'addEventListener' in window) {
               document.write('<script src="//ajax.aspnetcdn.com/ajax/jQuery/jquery-2.1.1.min.js"><\/script>');
            }
            else {
               document.write('<script src="//ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.min.js"><\/script>');
            }
         }
      </script>

      <script>(typeof JSON === 'object') || document.write('<script src="//cdnjs.cloudflare.com/ajax/libs/json3/3.3.2/json3.min.js"><\/script>')</script>
      <!--[if lt IE 9]><script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script><![endif]-->

      <?php if ( $debug ): ?>
         <script src="/themes/<?php print $theme; ?>/js/main.js"></script>
      <?php else: ?>
         <script src="/themes/<?php print $theme; ?>/js/min_1406956143.js"></script>
      <?php endif; ?>
      <!--END JS-->

      <!--BEGIN CSS-->
      <?php if ( $debug ): ?>
         <link href="/themes/<?php print $theme; ?>/css/normalize.css" rel="stylesheet" type="text/css" />
         <link href="/themes/<?php print $theme; ?>/css/font.css" rel="stylesheet" type="text/css" />
         <link href="/themes/<?php print $theme; ?>/css/main_xs.css" rel="stylesheet" type="text/css" />
         <link href="/themes/<?php print $theme; ?>/css/main_sm.css" rel="stylesheet" type="text/css" />
         <link href="/themes/<?php print $theme; ?>/css/main_md.css" rel="stylesheet" type="text/css" />
         <link href="/themes/<?php print $theme; ?>/css/main_lg.css" rel="stylesheet" type="text/css" />
      <?php else: ?>
         <link href="/themes/<?php print $theme; ?>/css/min_1406956143.css" rel="stylesheet" type="text/css" />
      <?php endif; ?>
      <!--END CSS-->

      <title><?php print $head_title; ?></title>
      <!--BEING APPLE ICON-->
      <link rel='apple-touch-icon' href='/apple-touch-icon.png' />
      <link rel='apple-touch-icon' sizes='72x72' href='/apple-touch-icon-72x72.png' />
      <link rel='apple-touch-icon' sizes='114x114' href='/apple-touch-icon-114x114.png' />
      <!--END APPLE ICON-->
   </head>
   <body>
      <div id='page'>
         <header>
            <div id='logo'>
               <a style='padding: 0pt; margin: 0pt; display: block; width: 60px; height: 60px;' href='/' title='首页' rel='home'>
                  <img src='/themes/roselife/images/logo.png' alt='首页' id='logo-image'>
               </a>
            </div>
         </header>
         <nav><?php print $page_navbar; ?></nav>
         <article><?php print $content; ?></article>>
         <footer>
            <div id='copyright'>如有问题，请联络网站管理员
               <a href="mailto:admin@houstonbbs.com">admin@houstonbbs.com</a> | © 2009-2014 HoustonBBS 版权所有 | <a href='/term'>免责声明</a>
            </div>
         </footer>
      </div>
      <div id='ad'>
         <!--BEGIN AD-->
         <div id="ad01" class="ad0" style="width:468px; height:60px;">
            <a href="http://www.llestudyusa.com" target="_blank"><img width="468px" height="60px" src="/data/ad/lles-1.jpg" alt="" /></a>
         </div>
         <div id="ad02" class="ad0" style="width:468px; height:60px; right:468px">
            <a href="http://www.har.com/AWS/aws.cfm?agent_number=634860" target="_blank"><img width="468px" height="60px" src="/data/ad/alicewang-1.gif" alt="" /></a>
         </div>
         <div id="ad11" class="ad1">
            <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
            <!-- HoustonBBS leaderboard -->
            <ins class="adsbygoogle"
                 style="display:inline-block;width:728px;height:90px"
                 data-ad-client="ca-pub-8257334386742604"
                 data-ad-slot="2768722489"></ins>
            <script>
      (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
         </div>
         <div id="ad12" class="ad1"><a href="http://jeffreyjinteam.com/" target="_blank"><img width="272px" height="90px" src="/data/ad/jinfei.gif" alt=""></a></div>
         <!--END AD-->
      </div>
   </body>
   <?php if ( !$debug ): ?>
      <script>
         (function(i, s, o, g, r, a, m) {
            i['GoogleAnalyticsObject'] = r;
            i[r] = i[r] || function() {
               (i[r].q = i[r].q || []).push(arguments)
            }, i[r].l = 1 * new Date();
            a = s.createElement(o), m = s.getElementsByTagName(o)[0];
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m)
         })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

         ga('create', 'UA-36671672-1', 'houstonbbs.com');
         ga('send', 'pageview');
      </script>
   <?php endif; ?>
</html>