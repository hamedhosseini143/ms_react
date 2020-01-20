import React, { Component } from 'react'
import { BrowserRouter as Router, Route } from "react-router-dom";
import Message from './Component/Message'

class App extends Component {
  constructor(props) {
    super(props)
    this.state = {
      route: '',
    }
  }
  componentDidMount() {
    this.getUrl();
  }
  getUrl() {
    fetch('/ms_react/getRoute')
      .then(response => response.json())
      .then(data => {
        this.setState({
          route:data
        })
      })
      .catch(error => console.error(error))
  }
  render() {
    console.log('test',`${this.state.route}ms/chat/:id`)
    return (
      <Router>
        <Route
          path={`${this.state.route}ms/chat/:id`}
         render={(props) => <Message {...props} />} />
      </Router>
    )
  }
}
export default App
