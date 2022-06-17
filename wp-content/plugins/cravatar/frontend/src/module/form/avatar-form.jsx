import {deleteImages, getImages, postAvatars, putAvatars, sendEmailCode} from "../axios/mainApi";
import {toast} from "react-toastify";
import {
    Button,
    Card,
    Col, Dropdown,
    Form, InputGroup,
    Row,
} from "react-bootstrap";
import {useEffect, useRef, useState} from 'react'
import HistoriesLoader from "../Loader";
import useCountDown from "../useCountDown";
import {CropperModal} from "../modal/CropperModal";
import {ImageGallery} from "../modal/image-gallery";
import ReactCodeInput from "react-code-input";


export function ChangeAvatar(props) {


    /*公共*/
    const [Imgsrc, setImgsrc] = useState()
    const [Imgid, setImgid] = useState()


    const [Images, setImages] = useState()
    const [active, setActive] = useState("")
    /*获取图片库*/
    const GetImage = () => {
        getImages().then(response => {
            const result = response.data.data;
            /*console.log(response.data.data);*/
            setImages(result)
        })
    }
    useEffect(() => {
        GetImage()
    }, [])

    /*修改图片*/
    const putavatars = (e) => {
        const avatar_id = e.target.parentNode.parentNode.id;
        const image_id = document.querySelector('.avatar-view.active img').getAttribute("id");

        putAvatars(image_id, avatar_id)
            .then(response => {
                    toast.success(response.data.message)
                    props.getAvatars()
                    document.querySelector('button[class="btn-close"]').click();
                }
            )

    };


    /*删除图片*/
    const delete_images = (e) => {
        const id = e.target.id;
        deleteImages(id)
            .then(response => {
                    toast.success(response.data.message)
                    GetImage()
                    setImgsrc(null)
                }
            );
    }


    const [file, setFile] = useState("");
    const [filebs, setFilebs] = useState("");
    const onFileInputChange = (e) => {
        if (e.target.files && e.target.files.length > 0) {
            setFile(e.target.files[0]);
            let files;
            if (e.dataTransfer) {
                files = e.dataTransfer.files;
            } else if (e.target) {
                files = e.target.files;
            }
            const reader = new FileReader();
            reader.onload = () => {
                setFilebs(reader.result);
            };
            reader.readAsDataURL(files[0]);
        }

        e.target.value = null;


    };


    return <>

        <Row as={"section"}>
            <Col lg={8} className={"p-3 image_list_left"}>
                {Images
                    ?
                    Images.length === 0
                        ?
                        <div className={"d-flex center h-100 flex-column"}>
                            <i className="fa-duotone fa-boxes-packing fa-5x mb-3"></i>
                            <p className={"text-muted"}>暂无图片，请上传图片</p>
                        </div>
                        :
                        <Row className={"row-cols-5 g-3"} id={"image_list"}>
                            {Images.slice(0, 20).map((item, index) =>
                                <Col key={index} className={""}>
                                    <Card className={"avatar-view " + (active === item ? 'active' : '')}>
                                        <li className={"img-box"} onClick={() => setActive(item)}
                                            id={item.id}>
                                            <img
                                                className={'img-fluid  '}
                                                id={item.id} src={item.url} alt="cravatar图片"
                                                onClick={(e) => {
                                                    setImgsrc(e.target.src);
                                                    setImgid(e.target.id)
                                                }}
                                                onError={(e) => {
                                                    e.target.onerror = null;
                                                    e.target.src = "https://litepress.cn/cravatar/wp-content/uploads/sites/9/2021/07/default.png"
                                                }}/>
                                        </li>
                                    </Card>
                                </Col>
                            )}
                        </Row>
                    : <HistoriesLoader/>
                }
            </Col>
            <Col className={"text-center"}>
                <Card className={"h-100"}>
                    <Card.Header
                        className={"d-flex justify-content-between align-items-center w-100 position-absolute border-0"}>
                        <small className="text-muted">{Imgsrc ? "图片详情" : "请选择一张图片"}</small>

                        <Dropdown className={""}>
                            <Dropdown.Toggle size={"sm"}
                                             className={"card-dropdown-btn rounded-circle btn-icon"}
                                             variant={"ghost-secondary"} disabled={!Imgsrc}><i
                                className="fa-duotone fa-trash-can"></i></Dropdown.Toggle>
                            <Dropdown.Menu align="end" className={"p-2"}>
                                <Dropdown.Header>是否删除这张图片？</Dropdown.Header>
                                <Dropdown.Item as={Button} onClick={delete_images} id={Imgid}><i
                                    className="fa-duotone fa-circle-check"></i> 是</Dropdown.Item>
                                <Dropdown.Item as={Button} id={Imgid}><i
                                    className="fa-duotone fa-circle-x"></i> 否</Dropdown.Item>
                            </Dropdown.Menu>
                        </Dropdown>


                    </Card.Header>
                    <Card.Body className={"pt-2 pb-0 d-flex center"}>

                        {Imgsrc ? <img className={"img-fluid"} alt={""} src={Imgsrc}/> :
                            <svg xmlns="http://www.w3.org/2000/svg" className={"p-1"} width="100" height="100"
                                 viewBox="0 0 400 525" fill="none">
                                <path
                                    d="M362.5 525H37.5C16.75 525 0 508.25 0 487.5V37.5C0 16.75 16.75 0 37.5 0H275L400 125V487.5C400 508.25 383.25 525 362.5 525Z"
                                    fill="#E7EAF3"/>
                                <path d="M400 125H275V0L400 125Z" fill="#F8FAFD"/>
                                <path d="M275 125L400 250V125H275Z" fill="#BDC5D1"/>
                                <path
                                    d="M314.5 405.5L254.25 330.25C252.75 328.25 250.5 327 248 327H244.5C242 327 239.5 328.25 238 330.25L218.75 354.5L178.75 298.5C177.25 296.25 174.75 295 172 295.25H168.75C166.25 295.25 163.75 296.5 162.25 298.5L85.25 405.5C83.5 408 83.5 411.5 85.25 414L87.5 417.75C89 420.25 91.5 421.75 94.25 421.75H305C307.75 421.75 310.5 420.25 311.75 417.75L314 414C316.25 411.5 316.25 408 314.5 405.5ZM115 400.75L170.75 323.5L194.75 400.75H115Z"
                                    fill="#377DFF"/>
                                <path
                                    d="M268.075 294.664C279.362 291.999 286.352 280.688 283.687 269.401C281.021 258.114 269.711 251.124 258.424 253.79C247.136 256.455 240.147 267.765 242.812 279.053C245.477 290.34 256.788 297.329 268.075 294.664Z"
                                    fill="#BDC5D1"/>
                                <input xmlns="" id="_w_pedant"/>
                                <script xmlns=""/>
                                <div xmlns="" id="edge-translate-notifier-container"
                                     className="edge-translate-notifier-center"/>
                            </svg>
                        }


                    </Card.Body>
                </Card>

            </Col>
        </Row>


        <div className="d-flex justify-content-center justify-content-sm-end gap-3 mt-4">

            <CropperModal file={file} filebs={filebs} getImage={() => GetImage()}
            />


            <>
                <Button variant={"outline-white"} className={"position-relative"}>
                    <input type="file" className="input-file" onChange={onFileInputChange} id="avatarInput"
                           name="avatar_file"
                           accept="image/*"/>
                    <i className="fa-duotone fa-camera-retro me-2"></i> 上传新的
                </Button>
                <Button variant={"primary"} onClick={(e) => {
                    putavatars(e)
                }}><i
                    className="fa-duotone fa-camera-retro me-2"></i> 选择图片</Button>
            </>

        </div>


    </>
}

export function PostAvatars(props) {

    const [validated, setValidated] = useState(false);
    const [content, setContent] = useState('获取验证码')
    const [btnDisabled, setBtnDisabled] = useState(false)
    const {count, run} = useCountDown()
    const [Emailcode, setEmailcode] = useState("");

    useEffect(() => {
        if (btnDisabled) {
            setContent(`重新发送 ${count}s`)
        }
        if (count === 0) {
            btnStatusReset()
        }
    }, [btnDisabled, count])

    const emailform = useRef(null);
    const email = useRef(null);


    // 邮箱验证码按钮点击
    function handleBtnClick(e) {
        e.preventDefault();

        setValidated(true);
        if (window.$("#email").is(":valid") === true) {
            window.$('.tncode').trigger("click");
            setValidated(false);
        }

        window.tncode.onsuccess(function () {
            toast.success("验证码已发送到您填写的邮箱上,有效期5分钟，请注意查收")
            setBtnDisabled(true)
            setContent(`重新发送 ${count}s`)
            run()

            sendEmailCode(email.current.value)
                .then(response => {
                    toast.success(response.data.message)
                })

        })
    }

    const handleEmailcode = Emailcode => {
        setEmailcode(Emailcode);
    };


    const btnStatusReset = () => {
        setContent('获取验证码')
        setBtnDisabled(false)
    }


    /*添加新头像接口*/

    const Select_Image = () => {
        const image_id = document.getElementsByClassName('image_id')[0].getAttribute("id");
        postAvatars(email.current?.value, Emailcode, image_id)
            .then(response => {
                toast.success(response.data.message)
                document.querySelector('.modal.show button[class="btn-close"]').click();
            })
    }

    return <>
        <Form id="bind-email-form mt-3" ref={emailform} noValidate validated={validated}>


            <Row className={"mb-4"}>
                <label htmlFor="newEmailLabel" className="col-lg-3 col-form-label form-label">绑定邮箱</label>
                <Col lg={9}>
                    <InputGroup>
                        <Form.Control ref={email}
                                      placeholder="name@example.com" id={"email"}
                                      aria-label="name@example.com"
                                      type="email" required autoFocus
                        />

                        <button className={"input-group-text right"} onClick={handleBtnClick}
                                disabled={btnDisabled} type="submit">{content}</button>
                        <Form.Control.Feedback type="invalid">
                            请输入正确的邮箱号
                        </Form.Control.Feedback>
                    </InputGroup>

                </Col>
            </Row>
            <Row>
                <label htmlFor="newEmailLabel" className="col-lg-3 col-form-label form-label">验证码</label>
                <Col lg={9}>

                    <ReactCodeInput autoFocus={false} fields={4}
                                    onChange={handleEmailcode}/>
                </Col>
            </Row>
            <Row className="row mt-4">
                <label className="col-sm-3 col-form-label form-label d-flex align-items-center">绑定图像</label>

                <Col className={"col-3"}>

                    <ImageGallery/>
                </Col>
            </Row>

            <div className="d-flex justify-content-center justify-content-sm-end gap-3 mt-4">

                <button type="button" className="btn btn-primary" onClick={Select_Image}><i
                    className="fa-duotone fa-rectangle-history-circle-user me-2"></i> 添加
                </button>
            </div>


        </Form>

    </>
}
