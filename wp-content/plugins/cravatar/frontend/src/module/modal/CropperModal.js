import Modal from "react-bootstrap/Modal";
import {useEffect, useState} from "react";
import {Cropper} from "react-cropper";
import "cropperjs/dist/cropper.css";
import {Button} from "react-bootstrap";
import {postImages} from "../axios/mainApi";
import {toast} from "react-toastify";


export function CropperModal(props) {
    const {file, filebs} = props;
    const [cropper, setCropper] = useState(null);

    const [image, setImage] = useState(null);

    useEffect((e) => {
        if (file !== null) {
            setImage("show");
        } else {
            setImage(null);
        }
    }, [file, cropper]);

    const handleClose = () => {
        setImage(null);
    }


    const onConfirm = () => {

        if (typeof cropper !== "undefined") {
            const canvasData: HTMLCanvasElement = cropper.getCroppedCanvas();

            canvasData.toBlob((blob) => {
                const croppedFile = new File([blob], file.name, {"type": blob.type, 'lastModified': new Date()});
 /*               console.log(file)
                console.log(croppedFile)*/
                const formData = new FormData();
                formData.append("image", croppedFile)
                postImages(formData)
                    .then(response => {
                            toast.success(response.data.message)
                            setImage(null);
                            props.getImage()
                        }
                    )
            }, file.name, props.quality);
        }
    }


    return (
        <>
            <Modal show={(!!file && !!image)} onHide={handleClose} className={"croppermodal"} size={props.modalsize}
                   backdrop="static" centered>
                <Modal.Header className={"border-0 pb-0"} closeButton>
                    <Modal.Title><i className="fa-duotone fa-crop"></i> 裁剪头像</Modal.Title>
                </Modal.Header>
                <Modal.Body className={"pt-0"}>
                    <div className={"p-3"} id={props.id}>

                        <Cropper
                            style={{height: 400, width: "100%"}}
                            zoomTo={0.5}
                            src={filebs}
                            initialAspectRatio={1}
                            preview=".img-preview"
                            viewMode={2}
                            minCropBoxHeight={10}
                            minCropBoxWidth={10}
                            background={true}
                            responsive={true}
                            autoCropArea={1}
                            checkOrientation={false} // https://github.com/fengyuanchen/cropperjs/issues/671
                            onInitialized={(instance) => {
                                setCropper(instance);
                            }}
                            guides={true}
                        />

                        {/*<img style={{ width: "100%" }} src={cropData} alt="cropped" />*/}
                        <div className="d-flex justify-content-center justify-content-sm-end gap-3 mt-4">
                            <Button variant={"primary"} onClick={onConfirm}><i
                                className="fa-duotone fa-crop"></i> 裁剪</Button>
                        </div>
                    </div>

                </Modal.Body>

            </Modal>

        </>
    )
}


