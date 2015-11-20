PHP-Git-Version-Tool
================
This class helps deal with remote repos and local repos you want to sync up. 
It handles tagged releases specifically allowing you to push up tagged version releases to a central repository.  In my case I built it because I had a centralized shared private repo of code that multiple other engineers would need to push their tagged version of code into. 

####Enjoy! I hope it helps others out!

> **Note:** 
> The server running commands will need to have git ssh permissions or use the credentials in the path to make the http request.  If you are having issues post an issue and I will try to help. 

**Override location of git binary**
(it assumes the binary */usr/bin/git*)
```
$git = new GitWrapper(); 
$git->setGit('/usr/local/bin/git'); 
```

**Add a remote directory to contain new code**

```
$git = new GitWrapper();
$git->add("/local/instance/of/package/registry",{$module->name}/{$module->version}/");
$git->commitAndPush(
    "/local/instance/of/package/registry",  // local instance of shared registry
    "{$module->name}/{$module->version}/",   // make changes and push to remote 
    "{$module->name} was registered systematically" // message for commit 
);
```

**Fetch a specific version of a remote repo and clone it somewhere local**
More specific version of how to use wrapper
```
function getVersion($path, $remoteUrl, $version = 'master') {        
    $git = new GitWrapper();
    $git->cloneRemoteNoCheckout($path, $remoteUrl);

    if($version !== 'master ) {
       $ref = $git->getTagRef($path, $version);
    } 

    $git->checkoutRef($path, $ref);
}
```

