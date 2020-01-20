import React, { Component } from 'react'
import Api from './Api'
import axios from 'axios';
import Datasort from 'react-data-sort'
import { animateScroll } from "react-scroll";
class Message extends Component {
  constructor(props) {
    super(props);
    this.state = {
      message: [],
      mid: this.props.match.params.id,
      msRoot: '',
      total: '',
      messageValue: '',
      messageUpdate: [],
      sendOnly: ''
    };
    this.handleChange = this.handleChange.bind(this);
    this.handleSubmit = this.handleSubmit.bind(this);
    // this.handleSubmitDeleteOne = this.handleSubmitDeleteOne.bind(this);
  }
  componentDidMount() {
    this.getMessage();
    this.msRoot();
    this.timer = setInterval(() => this.getMessage(), 5000);
    this.scrollToBottom();
  }
  componentWillUnmount() {
    this.timer = null;
  }
  componentDidUpdate() {
    this.scrollToBottom();
  }
  handleChange(event) {
    this.setState({ messageValue: event.target.value });
  }
  scrollToBottom() {
    animateScroll.scrollToBottom({
      containerId: "ms-messages"
    });
  }

  handleSubmit(event) {
    const data = {
      mid: this.state.mid,
      body: this.state.messageValue
    };
    axios.post(`/ms_react/creat_ms`, { data })
      .then(res => {
        this.setState(prevState => ({
          message: [res.data[0], ...prevState.message],
        }))
        this.setState({
          messageValue: ''
        })
      })
    event.preventDefault();
  }
  handleSubmitDeleteOne(item){
    axios.post(`/ms_react/deletone/${item}`)
    .then(res => {
      console.log("okkkkk")
    })
    event.preventDefault();
  }
  // get message root 
  async msRoot() {
    const apiHandler = new Api();
    await apiHandler
      .sendRequest('get', `ms_react/root/${this.state.mid}`)
      .then(result => {
        this.setState({
          msRoot: result.messageRoot,
          sendOnly: result.info,
        })
      })
      .catch(error => {
        console.log(error);
      });
  }
  // get message
  async getMessage() {
    const apiHandler = new Api();
    await apiHandler
      .sendRequest('get', `ms_react/getdata/${this.state.mid}`)
      .then(result => {
        this.setState({
          message: result.records,
          total: result.total
        })
      })
      .catch(error => {
        console.log(error);
      });
  }
  render() {
    if (this.state.total == 0) {
      return (
        <div className='chat_window'>
          <div className='top_menu'>
            <div className="title">
              {this.state.message.map((ms) => <div>{ms.ms_id}</div>)}
            </div>
          </div>
        </div>
      )
    } else {
      return (
        <Datasort
          data={this.state.message}
          defaultSortBy="time"
          render={({ data }) => (
            <div className='chat_window'>
              <div className='top_menu'>
                <div className="title">
                  {this.state.msRoot}
                </div>
              </div>
              <ul className='messages' id='ms-messages'>
                {data.map((ms) => (
                  
                  <li className={`message appeared ${ms.side}`} key={ms.ms_id}>
                    {ms.user_pick == '1' ? (
                      <div className="avatar">
                      </div>
                    ) : (
                        <div className="div-av-img">
                          <img className="ms-avatar-img" src={ms.user_pick}></img>
                        </div>
                      )}
                    <div className="text_wrapper">
                      <div className="text">{ms.body}</div>
                      <span className="ms-author">   {ms.timestamp}   </span>
                      <span className="ms-author">   {ms.author}   </span>
                      {console.log(ms)}
                      {ms.deletePermission == '1'?(
                        <span>
                          <button onClick={this.handleSubmitDeleteOne.bind(this, ms.ms_id)} > <i class="fa fa-eraser" aria-hidden="true"></i> </button>
                        </span>
                      ):
                      <span>
                      </span>
                      }
                      {ms.fileUrl != '1' ? (
                        <span className="ms-author-file">
                          <a href={ms.fileUrl}> <i className="fa fa-file" aria-hidden="true"> </i></a>
                        </span>
                      ) : (
                          <span> </span>
                        )}
                    </div>
                  </li>
                ))}
              </ul>
              <div className='bottom_wrapper clearfix'>
                {this.state.sendOnly == 0 ? (
                  <form onSubmit={this.handleSubmit}>
                    <div className='message_input_wrapper'>
                      <input type="text" value={this.state.messageValue} onChange={this.handleChange} className='message_input' placeholder="..."></input>
                    </div>
                    <button className="send_message"> ↵ </button>
                  </form>
                ) : <div></div>}
              </div>
            </div>

          )}
        />
      )
    }

  }
}
export default Message;