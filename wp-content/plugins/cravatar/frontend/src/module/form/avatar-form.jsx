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
                            <i className="fa-duotone fa-boxes-packing fa-5x mb-3 text-primary"></i>
                            <small className={"text-muted"}>暂无图片，请上传图片</small>
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
                            <i className="fa-duotone fa-file-image fa-5x text-primary"></i>
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
                <label className="col-sm-3 col-form-label form-label d-flex align-items-center">绑定图片</label>

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
