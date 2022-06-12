import Modal from 'react-bootstrap/Modal';
import Button from "react-bootstrap/Button";

import React, {forwardRef, useState} from 'react'



const BsModal =forwardRef((props,ref) => {

    const [show, setShow] = useState(false);

    const handleClose = () => setShow(false);
    const handleShow = () => setShow(true);




    return (
        <>
            <Button as={props.as} size={props.size}  variant={props.variant} className={props.class} onClick={handleShow}>
               <i className={props.icon}></i> {props.title}
            </Button>


            <Modal  show={show} onHide={handleClose} id="bind-modal" size={props.modalsize} backdrop="static" centered>
                <Modal.Header className={"border-0 pb-0"} closeButton>
                    <Modal.Title><i className={props.titleicon}></i> {props.modaltitle}</Modal.Title>
                </Modal.Header>
                <Modal.Body className={"pt-0"}>
                    <div className={"p-3"} id={props.id}>{props.body}</div>

                </Modal.Body>

            </Modal>
        </>
    );
})

export default BsModal
