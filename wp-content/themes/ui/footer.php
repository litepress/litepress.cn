<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <main class="form-signin">
                    <form>
                        <img class="mb-4" src="" alt="" width="72" height="57">
                        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

                        <div class="form-floating">
                            <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
                            <label for="floatingInput">Email address</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="floatingPassword" placeholder="Password">
                            <label for="floatingPassword">Password</label>
                        </div>

                        <div class="checkbox mb-3">
                            <label>
                                <input type="checkbox" value="remember-me"> Remember me
                            </label>
                        </div>
                        <button class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
                        <p class="mt-5 mb-3 text-muted">© 2017–2021</p>
                    </form>
                </main>
            </div>
            <div class="modal-footer">
                <!--<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>-->
                <button class="btn btn-primary btn-block btn-lg" type="submit">提交注册</button>
            </div>
        </div>
    </div>
</div>


<footer id="site-footer" role="contentinfo" class="header-footer-group wp-footer p-2 text-center">
    <div class="wp-rich-text">
        <p>
            © 2020-2022 <a href="https://litepress.cn">LitePress</a> 版权所有
            <a href="http://beian.miit.gov.cn" class="imprint" title="工信部备案" rel="nofollow" target="_blank">
                鲁ICP备2021028118号-2
            </a>
        </p>
    </div>

    <div class="deng-box">
        <div class="deng">
            <div class="xian"></div>
            <div class="deng-a">
                <div class="deng-b"><div class="deng-t">春</div></div>
            </div>
            <div class="shui shui-a"><div class="shui-c"></div><div class="shui-b"></div></div>
        </div>
    </div>

    <style>
        .deng-box {
            position: fixed;
            top: -40px;
            right: -20px;
            z-index: 9999999999999;
        }

        .deng-box1 {
            position: fixed;
            top: -30px;
            right: 10px;
            z-index: 999;
        }

        .deng-box1 .deng {
            position: relative;
            width: 120px;
            height: 90px;
            margin: 50px;
            background: #d8000f;
            background: rgba(216, 0, 15, 0.8);
            border-radius: 50% 50%;
            -webkit-transform-origin: 50% -100px;
            -webkit-animation: swing 5s infinite ease-in-out;
            box-shadow: -5px 5px 30px 4px rgba(252, 144, 61, 1);
        }

        .deng {
            position: relative;
            width: 120px;
            height: 90px;
            margin: 50px;
            background: #d8000f;
            background: rgba(216, 0, 15, 0.8);
            border-radius: 50% 50%;
            -webkit-transform-origin: 50% -100px;
            -webkit-animation: swing 3s infinite ease-in-out;
            box-shadow: -5px 5px 50px 4px rgba(250, 108, 0, 1);
        }

        .deng-a {
            width: 100px;
            height: 90px;
            background: #d8000f;
            background: rgba(216, 0, 15, 0.1);
            margin: 12px 8px 8px 10px;
            border-radius: 50% 50%;
            border: 2px solid #dc8f03;
        }

        .deng-b {
            width: 45px;
            height: 90px;
            background: #d8000f;
            background: rgba(216, 0, 15, 0.1);
            margin: -4px 8px 8px 26px;
            border-radius: 50% 50%;
            border: 2px solid #dc8f03;
        }

        .xian {
            position: absolute;
            top: -20px;
            left: 60px;
            width: 2px;
            height: 20px;
            background: #dc8f03;
        }

        .shui-a {
            position: relative;
            width: 5px;
            height: 20px;
            margin: -5px 0 0 59px;
            -webkit-animation: swing 4s infinite ease-in-out;
            -webkit-transform-origin: 50% -45px;
            background: #ffa500;
            border-radius: 0 0 5px 5px;
        }

        .shui-b {
            position: absolute;
            top: 14px;
            left: -2px;
            width: 10px;
            height: 10px;
            background: #dc8f03;
            border-radius: 50%;
        }

        .shui-c {
            position: absolute;
            top: 18px;
            left: -2px;
            width: 10px;
            height: 35px;
            background: #ffa500;
            border-radius: 0 0 0 5px;
        }

        .deng:before {
            position: absolute;
            top: -7px;
            left: 29px;
            height: 12px;
            width: 60px;
            content: " ";
            display: block;
            z-index: 999;
            border-radius: 5px 5px 0 0;
            border: solid 1px #dc8f03;
            background: #ffa500;
            background: linear-gradient(to right, #dc8f03, #ffa500, #dc8f03, #ffa500, #dc8f03);
        }

        .deng:after {
            position: absolute;
            bottom: -7px;
            left: 10px;
            height: 12px;
            width: 60px;
            content: " ";
            display: block;
            margin-left: 20px;
            border-radius: 0 0 5px 5px;
            border: solid 1px #dc8f03;
            background: #ffa500;
            background: linear-gradient(to right, #dc8f03, #ffa500, #dc8f03, #ffa500, #dc8f03);
        }

        .deng-t {
            font-family: 华文行楷,Arial,Lucida Grande,Tahoma,sans-serif;
            font-size: 3.2rem;
            color: #dc8f03;
            font-weight: bold;
            line-height: 85px;
            text-align: center;
        }

        .night .deng-t,
        .night .deng-box,
        .night .deng-box1 {
            background: transparent !important;
        }

        @-moz-keyframes swing {
            0% {
                -moz-transform: rotate(-10deg)
            }

            50% {
                -moz-transform: rotate(10deg)
            }

            100% {
                -moz-transform: rotate(-10deg)
            }
        }

        @-webkit-keyframes swing {
            0% {
                -webkit-transform: rotate(-10deg)
            }

            50% {
                -webkit-transform: rotate(10deg)
            }

            100% {
                -webkit-transform: rotate(-10deg)
            }
        }
        @media screen and (max-width: 676px) {
            .deng-box{
                display: none;
            }
        }
        @media screen and (max-width: 1643px) {
            .deng-box{
                right: auto;
                left: 20px;
                top: 22px;
            }
        }
    </style>

</footer><!-- #site-footer -->
<?php wp_footer(); ?>
</body>
</html>
