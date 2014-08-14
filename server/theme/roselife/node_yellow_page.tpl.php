<header class="content_header">
   <?php print $breadcrumb; ?>
   <span class='v_guest'>您需要先<a rel="nofollow" href="/user">登录</a>或<a href="/user/register">注册</a>才能发表新话题或回复</span>
   <button class='v_user' data-action="/node/<?php print $nid; ?>/comment">评论</button>
   <span class="ajax_load" data-ajax='<?php print $ajaxURI; ?>'><?php print $commentCount; ?> replies, <span class="ajax_viewCount_<?php print $nid; ?>"></span> views</span> 
   <?php print $pager; ?>
</header>

<article>   
   <div class="bcard">
      <ul class='clean'>
         <li data-before='地址'><?php print $node[ 'address' ]; ?></li>
         <li data-before='电话'><?php print $node[ 'phone' ]; ?></li>
         <?php if ( isset( $node[ 'fax' ] ) ): ?>
            <li data-before='传真'><?php print $node[ 'fax' ]; ?></li>
         <?php endif; ?>
         <?php if ( isset( $node[ 'email' ] ) ): ?>
            <li data-before='电子邮箱'><?php print $node[ 'email' ]; ?></li>
         <?php endif; ?>
         <?php if ( isset( $node[ 'website' ] ) ): ?>
            <li data-before='网站'><?php print $node[ 'website' ]; ?></li>
         <?php endif; ?>
      </ul>
   </div>
   <div class="article_content"><?php print $node[ 'HTMLbody' ] . $node[ 'attachments' ]; ?></div>
</article>

<?php if ( $comments ): ?>
   <div class="comments-node-type-yp" id="comments">
      <h2 id="comments-title">评论</h2>
      <?php foreach ( $comments as $index => $c ): ?>
         <a id="comment<?php print $c[ 'id' ]; ?>"></a>
         <article>      
            <header>
               <a href="/user/<?php print $c[ 'uid' ]; ?>"><?php print $c[ 'username' ]; ?></a> <span class='city'><?php print $c[ 'city' ]; ?></span> @ 
               <?php
               echo $c[ 'createTime' ];
               if ( $c[ 'lastModifiedTime' ] )
               {
                  echo ' (修改于 ' . $c[ 'lastModifiedTime' ] . ')';
               }
               ?>
               <?php if ( $c[ 'type' ] == 'comment' ): ?>
                  <span class="comment_num">#<?php print $postNumStart + $index ?></span>
               <?php endif; ?>
            </header>

            <div class="article_content"><?php print $c[ 'HTMLbody' ] . $c[ 'attachments' ]; ?></div>

            <footer class='v_user'>
               <div class="actions">
                  <?php $urole = 'v_user_tagadm_' . $tid . ' v_user' . $c[ 'uid' ]; ?>
                  <button class="edit <?php print $urole; ?>" data-action="<?php print '/' . $c[ 'type' ] . '/' . $c[ 'id' ] . '/edit'; ?>">编辑</button>
                  <button class="delete <?php print $urole; ?>" data-action="<?php print '/' . $c[ 'type' ] . '/' . $c[ 'id' ] . '/delete'; ?>">删除</button>
                  <button class="reply " data-action="/node/<?php print $nid; ?>/comment">回复</button>
                  <button class="quote" data-action="/node/<?php print $nid; ?>/comment">引用</button>
               </div>
               <div id="<?php print $c[ 'type' ] . '-' . $c[ 'id' ]; ?>-raw" style="display:none;">
                  <span class='username'><?php print $c[ 'username' ]; ?></span>
                  <pre class="postbody"><?php print $c[ 'body' ]; ?></pre>
                  <span class="files"><?php print $c[ 'filesJSON' ]; ?></span>
               </div>
            </footer>
         </article>
      <?php endforeach; ?>
   <?php endif; ?>

   <?php print $pager; ?>
   <?php print $editor; ?>