import React, { Component } from 'react'
import axios from 'axios';
export class App extends Component {
  constructor(props){
    super(props)
    this.state={
      messageCount:''
    }
  }
  componentDidMount() {
    this.getMessageCount();
    this.timer = setInterval(() => this.getMessageCount(), 5000);
  }
  componentWillUnmount() {
    this.timer = null;
  }
  getMessageCount() {
    axios.get(`/ms_react/notification`)
      .then(res => {
        this.setState({
          messageCount:res.data
        })
      })
  }
  render() {
    return (
      <div class="notification-list list-inline-item">
      <a class="nav-link arrow-none waves-effect" href="/ms_react/all" role="button" >
      <i class="far fa-envelope"></i>
        <span class="badge badge-pill badge-danger noti-icon-badge">{this.state.messageCount}</span>
      </a>
    </div>
    )
  }
}

export default App

