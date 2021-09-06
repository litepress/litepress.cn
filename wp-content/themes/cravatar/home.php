<?php
get_header();

$is_login = is_user_logged_in();
?>
    <main class="flex-fill">
        <section class="index-banner text-white">
            <div class="container container-2020 ">
                <div class="row justify-content-center justify-content-lg-between text-center text-lg-left   ">
                    <div class="col-12 col-md-10 col-lg-6 center index-banner-left"><h1
                                class="mb-20 mb-22-md mb-30-xxl 1">Cravatar - 互联网公共头像服务</h1>
                        <p class="subtitle mb-26-xs-md mb-30-md mb-40-xxl w-100 w-75-xxl text-center text-lg-left mx-auto mx-lg-0">

                            Cravatar 是 Gravatar 在中国的完美替代方案,<br>从此你可以自由的上传和分享头像。
                        </p>

                        <div class="d-flex justify-content-center justify-content-lg-start"><a
                                    class="btn btn-lg btn-light mt-4" data-offset="30"
                                    id="hgr-homepage-header-cta-get_started"
                                    href="<?php echo $is_login ? '/emails' : '/login' ?>">现在开始</a>

                        </div>
                        <small class="mt-4 g-color-auxiliary">Cravatar 对所有人永久免费</small>


                    </div>
                    <div class="col-lg-6 col-12  d-flex justify-content-center align-items-center ">
                        <img src="<?php echo CA_ROOT_URL; ?>/assets/img/background-header-image-101b1a9e9b.png">
                    </div>
                </div>
            </div>
        </section>

        <section class="section-spaces">

            <div class="container container-2020">
                <div id="trust-signals">
                    <h2 class="mb-5 text-center">你所认识的 WordPress 圈子牛人都在使用</h2>
                    <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center mb-3">
                        <div class="col">
                            <div class="card">
                                <a href="https://www.wpdaxue.com/cravatar.html" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                    <img class="uabb-gallery-img card-img" src="/wp-content/themes/cravatar/assets/img/wpdaxue.png" alt="WordPress大学" itemprop="thumbnail" title="WordPress大学">
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card">
                                <a href="https://zmingcx.com/" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                    <img class="uabb-gallery-img card-img" src="/wp-content/themes/cravatar/assets/img/zmingcx.png" alt="知更鸟" itemprop="thumbnail" title="知更鸟">
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card">
                                <a href="https://wpcom.cn/" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                    <img class="uabb-gallery-img card-img" src="/wp-content/themes/cravatar/assets/img/wpcom.png" alt="WPCOM" itemprop="thumbnail" title="WPCOM">
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card">
                                <a href="https://www.nicetheme.cn/" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                    <img class="uabb-gallery-img card-img" src="/wp-content/themes/cravatar/assets/img/nicetheme.png" alt="nicetheme" itemprop="thumbnail" title="nicetheme">
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card">
                                <a href="https://iro.tw/" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                    <img class="uabb-gallery-img card-img" src="/wp-content/themes/cravatar/assets/img/iro.png" alt="iro" itemprop="thumbnail" title="iro">
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center mb-3">
                        <div class="col">
                            <div class="card">
                                <a href="https://www.lovestu.com/" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                                    <img class="uabb-gallery-img card-img" src="/wp-content/themes/cravatar/assets/img/lovestu.png" alt="lovestu" itemprop="thumbnail" title="lovestu">
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card">
                                <a href="javascript:;" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes" style="" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    <p class=""><i class="fad fa-arrow-alt-up"></i> 我 要 上 榜</p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="section-spaces bg-bggradient">
            <div class="container">
                <div class="row">
                    <div class="col-xl-6">
                        <div class="g-icon g-icon--size-90 g-color-primary" role="presentation" aria-hidden="true">
                            <svg class="g-icon__content" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 90 48">
                                <path d="M24 0a24 24 0 1 1 0 48 24 24 0 0 1 0-48zm42 0a24 24 0 1 1-19.8 37.55c.38-.64.75-1.3 1.09-1.98a22.05 22.05 0 0 0 12.53 9.55c-2.05-2.05-3.77-5.1-4.97-8.78l-.22-.68-.06-.23-.23-.06-.68-.22a27.11 27.11 0 0 1-5.21-2.28c.23-.64.44-1.3.62-1.95 1.4.85 3.06 1.61 4.91 2.23l-.08-.36A41.93 41.93 0 0 1 53 25h-3.03a26.46 26.46 0 0 0 0-2H53c.06-2.74.37-5.36.89-7.79l.08-.36a24.4 24.4 0 0 0-4.91 2.23c-.18-.66-.39-1.3-.62-1.95a27.05 27.05 0 0 1 5.21-2.28l.68-.22.23-.06.06-.23.22-.68c1.2-3.69 2.92-6.73 4.97-8.78a22.05 22.05 0 0 0-12.53 9.55 23.8 23.8 0 0 0-1.1-1.98A23.97 23.97 0 0 1 66 0zm-9.15 36.02c1.84 5.49 4.87 9.24 8.15 9.88V37a41.93 41.93 0 0 1-7.79-.89l-.36-.08zm18.3 0-.36.08a41.7 41.7 0 0 1-7.79.89v8.91c3.28-.64 6.3-4.4 8.15-9.88zm11.97-5.84-.12.11c-2.04 2-5.04 3.69-8.66 4.86l-.68.22-.23.06-.06.23-.22.68c-1.2 3.69-2.92 6.73-4.97 8.78a22.05 22.05 0 0 0 14.94-14.94zM65 25h-9.99c.05 1.88.21 3.69.48 5.4l.13.76.14.74.15.73.28 1.18c.4.1.78.2 1.18.28l.73.15.74.14.75.13c1.72.27 3.53.43 5.4.48L65 25zm11.99 0H67v9.99c1.88-.05 3.69-.21 5.4-.48l.76-.13.74-.14.73-.15 1.18-.28c.1-.39.2-.78.28-1.18l.15-.73.14-.74.13-.75c.27-1.72.43-3.53.48-5.4zm-42.86-9.53L20 29.6 14.4 24l-1.87 1.87L20 33.33l16-16-1.87-1.86zM87.9 25H79a41.93 41.93 0 0 1-.89 7.79l-.08.36c5.49-1.84 9.24-4.87 9.88-8.15zM67 13.01V23h9.99a40.88 40.88 0 0 0-.48-5.4l-.13-.76-.14-.74-.15-.73-.28-1.18c-.4-.1-.78-.2-1.18-.28l-.73-.15-.74-.14-.75-.13a40.58 40.58 0 0 0-4.6-.45L67 13zm-2 0c-1.88.05-3.69.21-5.4.48l-.76.13-.74.14-.73.15-1.18.28c-.1.39-.2.78-.28 1.18l-.15.73-.14.74-.13.75a40.88 40.88 0 0 0-.48 5.41H65v-9.99zm13.02 1.84.08.36c.52 2.43.83 5.05.89 7.79h8.91c-.64-3.28-4.4-6.3-9.88-8.15zM72.18 2.88l.11.12c2 2.04 3.69 5.04 4.86 8.66l.22.68.06.23.23.06.68.22c3.69 1.2 6.73 2.92 8.78 4.97A22.05 22.05 0 0 0 72.18 2.88zM65 2.1c-3.28.64-6.3 4.4-8.15 9.88l.36-.08a41.7 41.7 0 0 1 7.79-.89V2.1zm2 0V11c2.74.06 5.36.37 7.79.89l.36.08C73.31 6.49 70.28 2.74 67 2.1z"></path>
                            </svg>
                        </div>
                        <h1 class="g-brand g-has-medium-font-size">与Gravatar API兼容</h1>
                        <p>为了降低你的使用成本，我们的API规范始终保持与 Gravatar 100%兼容</p>
                        <a class="btn btn-light mt-3 btn-lg" href="/developers">
                            开发文档
                        </a>
                    </div>
                    <div class="col-xl-6 g-pt-7">
                        <div class="g-icon g-icon--size-90 g-color-primary" role="presentation" aria-hidden="true">
                            <svg class="g-icon__content" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 90 48">
                                <path fill-rule="evenodd" d="M24 0a24 24 0 1 1 0 48 24 24 0 0 1 0-48zm42 0a24 24 0 1 1-19.8 37.55c.38-.64.75-1.3 1.09-1.98a22 22 0 1 0 0-23.15 25.4 25.4 0 0 0-1.1-1.97A23.97 23.97 0 0 1 66 0zm0 13.95a10 10 0 0 1 10 9.72v1.71a3.51 3.51 0 0 1-3.5 3.57 3.7 3.7 0 0 1-2.96-1.47A5 5 0 1 1 71 23.73v1.65c0 .79.71 1.57 1.5 1.57.75 0 1.42-.7 1.5-1.44v-1.56c0-4.34-3.66-8-8-8a8.1 8.1 0 0 0-8 8 8.1 8.1 0 0 0 7.76 8H71v2h-5a10 10 0 0 1 0-20zM28 18a6 6 0 1 1 0 12h-3v-2h3.24a3.99 3.99 0 0 0 2.59-6.83 3.99 3.99 0 0 0-2.6-1.16L28 20h-3v-2h3zm-5 0v2h-3.24a3.99 3.99 0 0 0-2.59 6.83 4 4 0 0 0 2.6 1.16L20 28h3v2h-3a6 6 0 1 1 0-12h3zm43 2.95a3 3 0 1 1 0 6 3 3 0 0 1 0-6zM28 23h-8v2h8v-2z"></path>
                            </svg>
                        </div>
                        <h1 class="g-brand g-has-medium-font-size">独有的三级头像匹配机制</h1>
                        <p>当访客请求自己的头像时，我们会按此顺序分三级匹配头像：Cravatar->Gravatar->QQ头像，这平均可以为70%的访客提供准确的头像</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="section-spaces">
            <div class="container">
        <h2 class="mb-5 text-center">认识我们的赞助商</h2>
        <div class="row row-cols-3 row-cols-xl-6 wp-img-ground justify-content-center mb-5">
            <div class="col">
                <div class="card">
                    <a href="https://www.weixiaoduo.com/?ref=sunxiyuan" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                        <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/weixiaoduo-logo-2020-300x134.png" alt="薇晓朵" itemprop="thumbnail" title="薇晓朵">
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <a href="https://www.yfxw.cn/" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                        <img class="uabb-gallery-img card-img" src="https://www.yfxw.cn/wp-content/uploads/2021/02/1613564243-bf130567ccd7e68.png" alt="源分享" itemprop="thumbnail" title="源分享">
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <a href="https://www.vpsor.cn?userCode=rh13788" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                        <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/logo.png" alt="硅云" itemprop="thumbnail" title="硅云">
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <a href="https://www.appnode.com/?jzgkdu" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                        <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/logo-s.gif" alt="AppNode" itemprop="thumbnail" title="AppNode">
                    </a>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <a href="https://console.upyun.com/register/?invite=SyMTvwEi_" target="_blank" rel="noopener" data-caption="" itemprop="contentUrl" one-link-mark="yes">
                        <img class="uabb-gallery-img card-img" src="/wp-content/uploads/2020/08/又拍云_logo5-300x153.png" alt="又拍云" itemprop="thumbnail" title="又拍云">
                    </a>
                </div>
            </div>
        </div>
            </div>
        </section>
        <section class="section-spaces  bg-black">
            <div class="container">
                <div class="row justify-content-center justify-content-md-between">
                    <div class="d-flex flex-column col-12 col-md-6 col-lg-5 text-center text-md-left justify-content-center order-md-2">
                        <p class="text-h-gray text-uppercase label opacity-6">致网站所有者和开发者：</p>
                        <h2 class="text-h-meteorite-dark mt-15 mt-25-xxl mb-20 mb-30-xxl">我们邀请你加入开放的公共头像标准</h2>
                        <p class="text-h-gray paragraph">正如 Gravatar 在国外做的那样，Cravatar 希望在中国建立一个开放的头像标准，你可以访问我的 <a href="/developers">开发者资源</a>，快速的在你的网站/应用中接入 Cravatar。</p></div>
                    <div class="d-flex justify-content-center align-items-center col-md-6 col-lg-6 mt-30 mt-50-sm mt-0-md order-md-1">
                        <div class="g-none g-block@M">
                            <!-- Editor mockup start -->
                            <div class="g-editor" role="presentation" aria-hidden="true">
                                <input class="hide" id="g-editor-1" type="radio" name="g-editor" tabindex="-1"
                                       checked="">
                                <input class="hide" id="g-editor-2" type="radio" name="g-editor" tabindex="-1">
                                <div class="g-editor-bar">
                                    <label class="g-editor-bar__tab" for="g-editor-1">example.php</label>
                                    <label class="g-editor-bar__tab" for="g-editor-2">example.js</label>
                                </div>
                                <div class="g-editor-content">
                                    <div class="g-editor-content__tab g-editor-content__tab--has-layout">
                                        <div class="g-editor-content__tab__line-numbers">1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>10<br>11<br>12<br>13
                                        </div>
                                        <pre class="g-editor-content__tab__code  language-php"><code
                                                    class=" language-php"><span
                                                        class="token keyword">function</span> <span
                                                        class="token function">get_cravatar_url</span><span
                                                        class="token punctuation">(</span> <span class="token variable">$email</span> <span
                                                        class="token punctuation">)</span> <span
                                                        class="token punctuation">{</span>
  <span class="token comment">// 邮箱转小写并去除首尾空格</span>
  <span class="token variable">$address</span> <span class="token operator">=</span> <span class="token function">strtolower</span><span
                                                        class="token punctuation">(</span> <span class="token function">trim</span><span
                                                        class="token punctuation">(</span> <span class="token variable">$email</span> <span
                                                        class="token punctuation">)</span> <span
                                                        class="token punctuation">)</span><span
                                                        class="token punctuation">;</span>

  <span class="token comment">// 获取邮箱的MD5哈希值</span>
  <span class="token variable">$hash</span> <span class="token operator">=</span> <span
                                                        class="token function">md5</span><span
                                                        class="token punctuation">(</span> $address <span
                                                        class="token punctuation">)</span><span
                                                        class="token punctuation">;</span>

  <span class="token comment">// 拼接出最终的头像URL</span>
  <span class="token keyword">return</span> <span class="token string single-quoted-string">'https://cravatar.cn/avatar/'</span> <span
                                                        class="token operator">.</span> <span class="token variable">$hash</span><span
                                                        class="token punctuation">;</span>
<span class="token punctuation">}</span></code></pre>

                                    </div>
                                    <div class="g-editor-content__tab">
                                        <div class="g-editor-content__tab__line-numbers">1<br>2<br>3<br>4<br>5<br>6<br>7<br>8<br>9<br>10<br>11<br>12<br>13<br>14
                                        </div>
                                        <pre class="g-editor-content__tab__code  language-js"><code
                                                    class=" language-js"><span
                                                        class="token keyword">const</span> md5 <span
                                                        class="token operator">=</span> <span class="token function">require</span><span
                                                        class="token punctuation">(</span> <span class="token string">'md5'</span> <span
                                                        class="token punctuation">)</span><span
                                                        class="token punctuation">;</span>

<span class="token keyword">function</span> <span class="token function">getCravatarURL</span><span
                                                        class="token punctuation">(</span> <span
                                                        class="token parameter">email</span> <span
                                                        class="token punctuation">)</span> <span
                                                        class="token punctuation">{</span>
  <span class="token comment">//  邮箱转小写并去除首尾空格</span>
  <span class="token keyword">const</span> address <span class="token operator">=</span> <span class="token function">String</span><span
                                                        class="token punctuation">(</span> email <span
                                                        class="token punctuation">)</span><span
                                                        class="token punctuation">.</span><span class="token function">trim</span><span
                                                        class="token punctuation">(</span><span
                                                        class="token punctuation">)</span><span
                                                        class="token punctuation">.</span><span class="token function">toLowerCase</span><span
                                                        class="token punctuation">(</span><span
                                                        class="token punctuation">)</span><span
                                                        class="token punctuation">;</span>

  <span class="token comment">// 获取邮箱的MD5哈希值</span>
  <span class="token keyword">const</span> hash <span class="token operator">=</span> <span
                                                        class="token function">md5</span><span
                                                        class="token punctuation">(</span> address <span
                                                        class="token punctuation">)</span><span
                                                        class="token punctuation">;</span>

  <span class="token comment">// 拼接出最终的头像URL</span>
  <span class="token keyword">return</span> <span class="token template-string"><span
                                                            class="token template-punctuation string">`</span><span
                                                            class="token string">https://cravatar.cn/avatar/</span><span
                                                            class="token interpolation"><span
                                                                class="token interpolation-punctuation punctuation">${</span> hash <span
                                                                class="token interpolation-punctuation punctuation">}</span></span><span
                                                            class="token template-punctuation string">`</span></span><span
                                                        class="token punctuation">;</span>
<span class="token punctuation">}</span></code></pre>
                                    </div>
                                </div>
                            </div>
                            <!-- Editor mockup end -->
                        </div>
                    </div>
                </div>
            </div>
    </main>

    <section class="section-spaces  section-spaces--bottom">
        <div class="container">
            <div class="row align-items-center justify-content-center flex-column">
                <div class="col-12 col-lg-6 d-flex flex-column align-items-center text-center">
                    <div id="carouselExampleCaptions" class="carousel slide carousel-fade" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="https://cravatar.cn/avatar/4c2a1e88031a2ea87817acfa2a11e058?s=400&amp;r=G&amp;d=mystery"
                                     class="gravatar avatar avatar-14 um-avatar um-avatar-gravatar" alt="耗子"
                                     onerror="if ( ! this.getAttribute('data-load-error') ){ this.setAttribute('data-load-error', '1');this.setAttribute('src', this.getAttribute('data-default'));}">
                            </div>
                            <div class="carousel-item">
                                <img src="https://cravatar.cn/avatar/245467ef31b6f0addc72b039b94122a4?s=400&amp;r=G&amp;d=mystery"
                                     class="gravatar avatar avatar-14 um-avatar um-avatar-gravatar" alt="孙锡源"
                                     onerror="if ( ! this.getAttribute('data-load-error') ){ this.setAttribute('data-load-error', '1');this.setAttribute('src', this.getAttribute('data-default'));}">
                            </div>
                            <div class="carousel-item">
                                <img src="https://cravatar.cn/avatar/526d5c35b2092765ce9865d807612f33?s=400&amp;r=G&amp;d=mystery"
                                     class="gravatar avatar avatar-14 um-avatar um-avatar-gravatar" alt="Yulinn"
                                     onerror="if ( ! this.getAttribute('data-load-error') ){ this.setAttribute('data-load-error', '1');this.setAttribute('src', this.getAttribute('data-default'));}">
                            </div>
                        </div>
                    </div>

                    <h2
                            class="text-h-meteorite-dark mt-4">更快速的头像以及更好用的WordPress！</h2>

                    <div class="d-flex justify-content-center justify-content-lg-start"><a
                                class="btn btn-lg btn-primary m-4" data-offset="30"
                                id="hgr-homepage-header-cta-get_started"
                                href="<?php echo $is_login ? '/emails' : '/login' ?>"
                        >立即创建你的头像</a>
                    </div>
                    <p class="paragraph mt-15 mt-20-sm mt-30-xxl container-2020-sm container-2020-xl-new">
                        Cravatar 当前由 LitePress.cn 提供维护支持，LitePress.cn 诞生的目的是为<br> WordPress
                        在中国搭建起稳定运行所需的所有基础设施，并使其完全本土化。
                    </p>
                </div>
            </div>
        </div>
    </section>
<?php
get_footer();