import { useState, useEffect, useRef } from "react";
// let timerId
export default function useCountDown(initCount = 60) {
    const [count, setCount] = useState(() => initCount)
    const timerId = useRef(null)

    // 设置清除定时器,避免count还未为0时，组件已被Unmount
    useEffect(() => {
        return () => {
            clearInterval(timerId.current)
        }
    }, [])

    // 监听count的变化
    useEffect(() => {
        if (count === 0) {
            clearInterval(timerId.current)
            setCount(60)
        }
    }, [count])
    // 定义定时器，每秒减一
    function run () {
        timerId.current = setInterval(() => {
            setCount(pre => pre - 1)
        }, 1000)
    }
    return {count, run}
}