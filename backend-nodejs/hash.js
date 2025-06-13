const bcrypt = require('bcrypt');
bcrypt.hash('panitia123', 10, (err, hash) => {
  if (err) throw err;
  console.log(hash);
});
