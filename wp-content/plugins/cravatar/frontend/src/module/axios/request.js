import axios from "axios";
import {toast} from "react-toastify";

const service = axios.create({
    baseURL:  window.wpApiSettings.root, // api的base_url
    timeout: 60 * 1000, // 请求超时时间
    headers: {
        'X-WP-Nonce': window.wpApiSettings.nonce,
        'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvZGV2LmxpdGVwcmVzcy5jblwvIiwiaWF0IjoxNjU0NzA1MzQyLCJuYmYiOjE2NTQ3MDUzNDIsImV4cCI6MTY1NTMxMDE0MiwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMjUifX19.-JiWsqmfhcU_AWkB8xz1Bl3AKzoztXxmnOEeiVlHNgA'
    },

})

// 添加请求拦截器)
service.interceptors.request.use(config => {
    // 在发送请求之前做些什么
    toast.loading("加载中……", {
        toastId: "loading"
    });
    return config;
}, error => {
    toast.dismiss();
    // 对请求错误做些什么
    return Promise.reject(error);
});


//响应拦截器
service.interceptors.response.use(response => {
    toast.dismiss();
    return response;
}, error => {
    toast.dismiss();
    return Promise.reject(error);
})

export default service;

