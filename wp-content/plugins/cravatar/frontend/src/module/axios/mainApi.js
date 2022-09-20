import request from './request'

/*发送邮件验证码*/
export function  sendEmailCode(emailval){
    return request({
        url: '/user/wp-json/common/send-email-code ',
        method: 'post',
        data: {email:emailval}
    })
}

/*获取当前用户全部头像*/
export  function getAvatars()  {
    return request({
        url: '/cravatar/avatars',
        method: 'get',
    })
}
/*添加新头像接口*/
export  function postAvatars(email,email_code,image_id)  {
    return request({
        url: '/cravatar/avatars',
        method: 'post',
        data: {email, email_code, image_id}
    })
}

/*修改头像接口*/
export function  putAvatars(image_id,avatar_id){
    return request({
        url: '/cravatar/avatars/'+avatar_id,
        method: 'put',
        data: {image_id: image_id}
    })
}


/*删除头像接口*/
export function  deleteAvatars(id){
    return request({
        url: '/cravatar/avatars/'+id,
        method: 'delete',
    })
}

// 图片管理相关接口
/*获取当前用户全部图片*/
export  function getImages()  {
    return request({
        url: '/cravatar/images',
        method: 'get',
    })
}

/*添加新图片*/
export  function postImages(formData)  {
    return request({
        url: '/cravatar/images',
        method: 'post',
        data:formData
    })
}

/*删除头像接口*/
export function  deleteImages(id){
    return request({
        url: '/cravatar/Images/'+id,
        method: 'delete',
    })
}
