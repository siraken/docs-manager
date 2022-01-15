const axiosBase = require('axios');
const axios = axiosBase.create({
  baseURL: 'http://localhost:8765', // バックエンドB のURL:port を指定する
  headers: {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  },
  responseType: 'json'  
});

const slipSetter = {

  /**
   * 
   */
  status(el, type, id, currentStatus) {
    const csrf = document.getElementsByName('_csrfToken')[0].value;
    axios.post('/estimates/setStatus', {
      type: type,
      id: id,
      currentStatus: currentStatus,
      _csrfToken: csrf
    })
    .then((res) => {
      if (res.data.status === 200) {
        location.reload();
      } else {
        console.log('Failed')
      }
    })
  }
}

window.slipSetter = slipSetter;
