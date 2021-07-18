<?php
/**
 * 个人实名模板
 *
 * @package WP_REAL_PERSON_VERIFY
 */
?>

<article class="container step-personal theme-boxshadow bg-white"">
<h1 class="title"><i class="fad fa-user-tie"></i> 个人认证</h1>
<small class="des">
    本次认证通过调用阿里云的实人认证API接口自动完成<br>
    如果认证时遇到问题，请联系客服QQ: 1642491905
</small>
<hr class="dropdown-divider">
    <form class="step-personal-form mt-4 needs-validation" novalidate>
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="realname" placeholder="真实姓名" required pattern="^([\u4e00-\u9fa5]{1,20}|[a-zA-Z\.\s]{1,20})$">
            <label for="floatingInput" class="col-sm-2 col-form-label">真实姓名</label>
            <div class="invalid-feedback">
                请输入真实姓名。
            </div>
        </div>
        <div class="form-floating">
            <input type="text" class="form-control" id="cert_no" placeholder="身份证号" required pattern="^[1-9]\d{5}(18|19|20|(3\d))\d{2}((0[1-9])|(1[0-2]))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$">
            <label for="floatingInput">身份证号</label>
            <div class="invalid-feedback">
                请输入真实身份证号码。
            </div>
        </div>

        <!-- Button trigger modal -->
        <button type="button" value="button" id="ali-authentication-btn" class="btn btn-primary">
            前往微信扫脸
        </button>
        <!-- Modal -->
        <div class="modal fade " id="ali-authentication-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" >微信扫脸认证</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" ></button>
                    </div>
                    <div class="modal-body">
                        <p>请扫描二维码前往微信扫脸认证</p>
                        <small class="authentication-message fade show alert-warning">获取中<i class="loading"></i></small>
                        <div id="qrcode" class="mt-3"></div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" >我已完成认证</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

</article>

<script src="https://cdn.bootcdn.net/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>

