import React, { Component } from "react";

export default class Api {
  constructor() {
    this.endpoint = "/";
  }
  /**
   *
   *creat node servicesss
   * @param {*} data
   * @returns
   * @memberof Api
   */

  /**
   *
   *for post methhod api
   * @param {*} path
   * @param {*} data
   * @param {*} validStatus
   * @param {*} returnAll
   * @returns
   * @memberof Api
   */
  async sendRequest(method, path, data) {
    const request = new Request(this.endpoint + path, {
      method,
      body: JSON.stringify(data)
    });
    return fetch(request)
      .then(response => {
          return response.json();
      })
      .catch(error => {
        console.log(error);
        return error;
      });
  }
}
