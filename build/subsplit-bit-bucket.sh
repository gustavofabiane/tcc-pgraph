
git subsplit init https://bitbucket.org/gustavofabiane/framework.git
git subsplit publish --heads="master" src/Container:https://bitbucket.org/gustavofabiane/container.git
rm -rf .subsplit/