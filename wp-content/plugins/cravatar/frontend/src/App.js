import './App.css';
import {Button, Card, Col, Nav, Row, Table} from "react-bootstrap";
import {useEffect, useRef, useState} from "react";
import {deleteAvatars, getAvatars} from "./module/axios/mainApi";
import {toast} from "react-toastify";
import BsModal from "./module/Modal/modal";
import {
    ChangeAvatar, PostAvatars
} from "./module/form/avatar-form";



export default function App() {
    const [Users, setUsers] = useState()
    const GetAvatars = () => {
        getAvatars().then(response => {
            const result = response.data.data;
            /*console.log(response.data.data);*/
            setUsers(result)
        }).catch(error => {
            if (error.response) {
                toast.warn(error.response.data.message)
            } else if (error.request) {
                console.log(error.request);
            } else {
                console.log('Error', error.message);
            }
        });
    }
    useEffect(() => {
        GetAvatars()
    }, [])


    /*删除头像接口*/
    const deleteavatars = (e) => {
        const id = e.target.id;
        deleteAvatars(id)
            .then(response => {
                    toast.success(response.data.message)
                    GetAvatars()
                }
            )
            .catch(error => {
                if (error.response) {
                    toast.warn(error.response.data.message)
                } else if (error.request) {
                    console.log(error.request);
                } else {
                    console.log('Error', error.message);
                }
            });
    };


    return (
        <>

            <Card>
                <Card.Header className={"p-4 bg-white"}>
                    <Card.Title>
                        <span className={"h5"}>管理头像</span>
                    </Card.Title>
                </Card.Header>
                <Card.Body className={"p-4"}>
                    <Card.Text>头像由图片与邮箱绑定而成，你可以通过在 <code
                        className={"text-primary"}>https://cravatar.cn/avatar/后方拼接邮箱md5</code> 的方式访问到你的头像。</Card.Text>

                    <Row>
                        <Col lg={9}>
                            <Table variant={""} borderless responsive className={"align-middle m-0"}>
                                <thead className={"thead-light"}>
                                <tr>
                                    <th className={"text-center"}>图片</th>
                                    <th>邮箱</th>
                                    <th className={"text-end"}>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                {Users && Users.map((user, index) => <tr key={index}>
                                    <td className={"text-center"}><img
                                        className="img-fluid lp-avatar"
                                        src={user.image}
                                        alt=""/></td>
                                    <td>{user.email}</td>
                                    <td>
                                        <div
                                            className="d-flex gap-3 justify-content-center justify-content-lg-end flex-wrap">
                                            <BsModal size={"sm"} modalsize={"lg"} id={user.id}
                                                     variant={"outline-white"}
                                                     titleicon={'fa-duotone me-2 fa-circle-user'}
                                                     icon={"fa-duotone fa-pen-to-square"} title={'更换图片'}
                                                     modaltitle={"图片库"}
                                                     body={<ChangeAvatar avatar={user.image} getAvatars={()=> GetAvatars()}   />}/>

                                            <Button size="sm" id={user.id} onClick={deleteavatars}
                                                    variant="outline-white" className="">
                                                <i className="fa-duotone fa-trash-can"></i> 删除
                                            </Button>
                                        </div>
                                    </td>
                                </tr>)}
                                </tbody>
                            </Table>

                            <BsModal as={"li"} modalsize={"md"} id={""} variant={""}
                                     class={"card card-body card-dashed-body card-dashed text-center mt-3 text-primary"}
                                     titleicon={'fa-duotone fa-rectangle-history-circle-user'}
                                     icon={"fa-duotone fa-rectangle-history-circle-user fa-2x mb-2"} title={'添加图片'}
                                     modaltitle={"添加图片"}
                                     body={<PostAvatars/>}/>
                        </Col>
                        <Col className={"border-start"}>
                            <Nav className="nav-tabs-user nav-tabs flex-column mb-3">
                                <Nav.Item>

                                    <Nav.Link active={false}>
                                        <i className="fa-duotone fa-rectangle-history-circle-user fa-fw me-2"></i> 图片库
                                    </Nav.Link>

                                </Nav.Item>
                                <Nav.Item>

                                    <Nav.Link active={false}>
                                        <i className="fa-duotone fa-display-code fa-fw me-2"></i> 开发文档
                                    </Nav.Link>

                                </Nav.Item>
                                <Nav.Item>

                                    <Nav.Link active={false}>
                                        <i className="fa-duotone fa-comments-question-check fa-fw me-2"></i> 常见问题
                                    </Nav.Link>

                                </Nav.Item>
                            </Nav>
                        </Col>
                    </Row>


                </Card.Body>


            </Card>

        </>
    );
}
