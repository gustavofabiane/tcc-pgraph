
git subsplit init https://bitbucket.org/gustavofabiane/framework.git
git subsplit publish --heads="master" src/container:https://bitbucket.org/gustavofabiane/container.git
rm -rf .subsplit/