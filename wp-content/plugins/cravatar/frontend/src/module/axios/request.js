import axios from "axios";
import {toast} from "react-toastify";

const service = axios.create({
    baseURL:  window.wpApiSettings.root, // api的base_url
    timeout: 60 * 1000, // 请求超时时间
    headers: {
        'X-WP-Nonce': window.wpApiSettings.nonce,
       /* 'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvZGV2LmxpdGVwcmVzcy5jblwvIiwiaWF0IjoxNjU0NzA1MzQyLCJuYmYiOjE2NTQ3MDUzNDIsImV4cCI6MTY1NTMxMDE0MiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjUifX19.-JiWsqmfhcU_AWkB8xz1Bl3AKzoztXxmnOEeiVlHNgA'
    */},

})

/*// 添加请求拦截器)
service.interceptors.request.use(config => {
    // 在发送请求之前做些什么
/!*    toast.loading("加载中……", {
        toastId: "loading"
    });*!/
    return config;
}, error => {
    /!*toast.dismiss();*!/
    // 对请求错误做些什么
    return Promise.reject(error);
});*/


//响应拦截器
service.interceptors.response.use(response => {
/*响应成功*/
    return response;
}, error => {
    /*响应错误*/
    const status =
        (error.response &&
            error.response.status &&
            error.response.status) ||
        '';
    const data = (error.response && error.response.data) || {};
    if (data.message) {
        toast.warn(data.message);
        return Promise.reject(data.message);
    }

    if (
        error.code === 'ECONNABORTED' &&
        error.message.indexOf('timeout') !== -1
    ) {
        toast.warn('请求超时~~');
        return Promise.reject('请求超时~~');
    }
    if (status === 401) {
        toast.warn('登录过期,请重新登录');
        return Promise.reject('登录过期,请重新登录');
    }
    if (status === 404) {
        toast.warn('接口404报错');
        return Promise.reject('接口404报错');
    }
    if (status === 500) {
        console.log(error.response);
        toast.warn('服务器错误');
        return Promise.reject('接口404报错');
    }
    return Promise.reject(error);
})

export default service;

