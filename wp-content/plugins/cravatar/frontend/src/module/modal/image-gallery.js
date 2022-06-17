import Modal from "react-bootstrap/Modal";
import {forwardRef, useImperativeHandle, useState, useEffect} from "react";
import {Button, Card, Col, Dropdown, Row} from "react-bootstrap";
import HistoriesLoader from "../Loader";
import {CropperModal} from "./CropperModal";
import {deleteImages, getImages} from "../axios/mainApi";
import {toast} from "react-toastify";


export const ImageGallery = forwardRef((props, ref) => {

    /*公共*/
    const [Imgsrc, setImgsrc] = useState()
    const [activeImg, setactiveImg] = useState("https://dev.litepress.cn/cravatar/wp-content/uploads/sites/9/2022/06/未标题-1-1.png")
    const [activeImgid, setactiveImgid] = useState("112")
    const [Imgid, setImgid] = useState()


    const [Images, setImages] = useState()
    const [active, setActive] = useState("")
    /*获取图片库*/
    const GetImage = () => {
        getImages().then(response => {
            const result = response.data.data;
            console.log(response.data.data);
            console.log(result.length===0);
            setImages(result)
        })
    }
    useEffect(() => {
        GetImage()
    }, [])


    /*删除图片*/
    const delete_images = (e) => {
        const id = e.target.id;
        deleteImages(id)
            .then(response => {
                    toast.success(response.data.message)
                    GetImage()
                    setImgsrc(null)
                }
            )
    }

    const SelectImage = (e) => {
        const nav = document.querySelector('.avatar-view.active img');
        const id = nav.getAttribute("id")
        const scr = nav.getAttribute("src")

        setactiveImgid(id)
        setactiveImg(scr)
        setShow(false);
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


    //将子组件的方法 暴露给父组件
    useImperativeHandle(ref, () => ({
        handleShow
    }))
    const [show, setShow] = useState(false);
    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);


    return <>
        <Card className={"avatar-view"} onClick={handleShow}>
            <img className="img-fluid image_id"
                 src={activeImg} id={activeImgid}
                 alt="cravatar图片"/>
            <span className="avatar-tooltip"><i className="fad fa-camera-retro"></i></span>
        </Card>
        <Modal show={show} onHide={handleClose} className={"croppermodal"} size={"lg"}
               backdrop="static" centered>
            <Modal.Header className={"border-0 pb-0"} closeButton>
                <Modal.Title><i className="fa-duotone me-2 fa-circle-user"></i>图片库</Modal.Title>
            </Modal.Header>
            <Modal.Body className={"pt-0"}>
                <div className={"p-3"}>

                    <Row as={"section"}>
                        <Col lg={8} className={"p-3 image_list_left"}>
                            {Images
                                ?
                                Images.length === 0
                                    ?
                                    <div className={"d-flex center h-100 flex-column"}>
                                        <i className="fa-duotone fa-boxes-packing fa-5x mb-3 placeholder-img"></i>
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

                                    {Imgsrc ? <img className={"img-fluid"} src={Imgsrc} alt={""}/> :
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
                            <Button variant={"primary"} onClick={SelectImage}><i
                                className="fa-duotone fa-camera-retro me-2"></i> 选择图片</Button>
                        </>

                    </div>

                </div>
            </Modal.Body>

        </Modal>
    </>
})




