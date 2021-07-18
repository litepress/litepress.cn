<?php
?>
<div class="wrap">
    <h1>证书管理</h1>
    <div id="message" class="notice-info notice">
        <p><strong>你知道吗？</strong></p>
        <p>
            为保证授权系统的安全性，我们使用RSA证书对数据进行签名，你可以从这个列表复制证书公钥到你的应用中，参考此<a href="https://litepress.cn/docs/archives/%e5%ba%94%e7%94%a8%e5%b8%82%e5%9c%ba%e5%85%a5%e9%a9%bb%e5%b8%ae%e5%8a%a9/%e5%af%b9%e6%8e%a5%e6%8e%88%e6%9d%83%e7%b3%bb%e7%bb%9f/%e5%bf%ab%e9%80%9f%e5%af%b9%e6%8e%a5" target="_blank">教程</a>。
            <br/>提示：如果你的应用被黑客解密导致公钥泄露，可以在此页面创建一个新的证书，并在新的应用版本中使用；但请不要轻易删除老证书，否则会导致用户无法激活使用此证书的应用版本。
        </p>
    </div>
    <section class="lprsa">
        <li>
            <label><b>证书ID</b></label>
            <aside>
                <b>
                    证书密钥
                </b>
            </aside>
            <P class="right"><b>操作</b></P>
        </li>
		<?php foreach ( $rsa_list as $rsa ): ?>
            <li>
                <label><?php echo $rsa['id'] ?></label>
                <aside>
                <textarea rows="5" cols="45" class="-text" readonly="readonly"
                ><?php echo $rsa['public_key'] ?></textarea>
                </aside>
                <div class="right">
                    <button class="copy button">复制</button>
                    <form method="post">
                        <input hidden name="method" value="delete"/>
                        <input hidden name="id" value="<?php echo $rsa['id'] ?>"/>
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="删除">
                    </form>
                </div>
            </li>
		<?php endforeach; ?>
        <footer>
            <form method="post">
                <input hidden name="method" value="create"/>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="创建新的证书">
                </p>
            </form>
        </footer>
    </section>
</div>