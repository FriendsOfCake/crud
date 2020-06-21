const { exec } = require("child_process");

const docker = "docker run --rm -v $(pwd):/data friendsofcake/crud make html";

module.exports = {
  server: {
    baseDir: "_build/html",
    index: "index.html"
  },

  files: [
    "_build/html/*.html",
    {
      match: ["**/*.rst", "**/*.php"],
      fn: (event) => {
        if (event !== 'change') {
          return false;
        }
        exec(docker, (error, stdout, stderr) => {
          if (error) {
            console.error(error);
            return;
          }
          console.log(stdout);
        });
        return false;
      },
      options: {
        ignored: '_build'
      }
    }
  ]
};
