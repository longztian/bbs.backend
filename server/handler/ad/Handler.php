<?php

declare(strict_types=1);

namespace site\handler\ad;

use lzx\html\HtmlElement;
use site\Controller;

class Handler extends Controller
{
    public function run(): void
    {
        $form_yp = <<<'YP'
 <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="PEP9ASKEHJV7Q">
<table>
<tr><td><input type="hidden" name="on0" value="Time">广告时间</td></tr><tr><td><select name="os0">
  <option value="3 months">3个月 $240</option>
  <option value="6 months">6个月 $420</option>
  <option value="12 months">12个月 $720</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
YP;

        $form_banner = <<<'HEAD'
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="N2NJXZZRT69RS">
<table>
<tr><td><input type="hidden" name="on0" value="Time">广告时间</td></tr><tr><td><select name="os0">
  <option value="3 months">3个月 $800</option>
  <option value="6 months">6个月 $1,400</option>
  <option value="12 months">12个月 $2,400</option>
</select> </td></tr>
</table>
<input type="hidden" name="currency_code" value="USD">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
HEAD;

        $content = new HtmlElement('div');

        $form = new HtmlElement('div', null, ['style' => 'padding:1em;']);
        $form->addData(new HtmlElement('h3', '商家黄页广告'));
        $form->addData($form_yp);
        $content->addData($form);

        $form = new HtmlElement('div', null, ['style' => 'padding:1em;']);
        $form->addData(new HtmlElement('h3', '页顶图片广告'));
        $form->addData($form_banner);
        $content->addData($form);

        $this->html->setContent($content);
    }
}
