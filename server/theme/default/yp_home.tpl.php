<div id="content">

  <div id="content-area">
    <div class="node node-type-page" id="node-2500"><div class="node-inner">

        <div class="content">
          <style type="text/css">
          li.l1
          {
            float: left;
            display: block;
            width:33%;
            height: 7em;
            list-style: none;
          }
          .l1 a
          {
            padding: 1px 5px;
          }
          .l1 a:hover
          {
            background-color: #ccc;
          }
          .l1 ul
          {
            padding-left: 10px;
          }
          .l2
          {
            display: block;
            float:left;
            list-style: none;
          }
          </style>
          <a style="display: block; margin: 10px 10px 10px 5px; background-color: rgb(183, 252, 183); border: 1px solid rgb(122, 201, 122); padding: 5px; text-align: center;" href="/yp/join">加入黄页</a>

          <ul style="padding-left: 1.5em;">
            <?php foreach ($yp['children'] as $group): ?>
              <li class="l1">
                <a title="<?php echo $group['description']; ?>" href="/yp/<?php echo $group['tid']; ?>"><?php echo $group['name']; ?></a>
                <ul>
                  <?php foreach ($group['children'] as $tag): ?>
                    <li class="l2"><a title="<?php echo $tag['description']; ?>" href="/yp/<?php echo $tag['tid']; ?>"><?php echo $tag['name']; ?></a></li>
                    <?php endforeach; ?>
                </ul>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div></div> <!-- /node-inner, /node -->
  </div>

</div>