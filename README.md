## Bitbucket changelog reader

Get commit messages from your bitbucket server
 - organized by committer
 - filterable using commit ids
 - filterable using tags
 - default output json
 - output formatted as textual message
 
### Usage

```
docker run --rm -e BITBUCKET_URL="https://bitbucket-server-url.com" -e USERNAME="user" -e PASSWORD="password" myposter/bitbucket-changelog-reader -p "PROJECT" -r "repo"
```

Required options

 - `-p | --project {project-key}`
 - `-r | --repo {repository-slug}`
 
Additional options

 - `-m | --excludeMerges`
 - `-o | --outputText`
 - `-s | --since {commit-id}`
 - `-u | --until {commit-id}`
 - `-f | --tagFrom {tag-name}`
 - `-t | --tagTo {tag-name}`
