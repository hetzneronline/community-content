# Hetzner Tutorial Guidelines

## General Criteria
* Tutorials should be in English.
  * If you're fluent in another language, and are able to make a translation, you can submit your tutorial in multiple languages, as long as at least one is English.
  * If you're not fluent in English, but you have a great tutorial to share in a different language, please reach out to us. We are open to the possibility of making exceptions for specific tutorials.
* We will only accept original work.
   * This means that you cannot re-submit any work that is published somewhere else on the the web.
* If your tutorial requires a server it should work on a new server.
   * If a user has just ordered a server, they should be able to follow the tutorial step by step, without first having to install or configure anything. If that is a prerequisite for your tutorial though, please make sure there is already a tutorial explaining that, and then make sure to link to that other tutorial at the beginning of yours.
* Write in a clear, easy to understand way.
   * These tutorials will be read by users with a wide range of experience. Make sure beginners can still follow what is being done. This means it is important not to skip any steps, no matter how obvious or self-explanatory they may seem. Feel free to include screenshots, to show exactly what the user should be seeing.
   * If you use acronyms, make sure to write them out the first time you use them.
   * Don't use excessive jargon or techspeak. Again, if you do use a word that not everybody might understand, either explain it, or use an easier to understand word or phrase.
   * Jokes are allowed, but don't overdo it.

## How to 

1. Fork the project
2. Add your tutorial folder:
   `mkdir -p tutorials/my-tutorial-name`
3. Add the templates:
   `cat tutorial-template.md > tutorials/my-tutorial-name/01.en.md`
4. Write the content
5. Create a pull request and include the following in your request:

```
I have read and understood the Contributor's Certificate of Origin available at the end of 
https://raw.githubusercontent.com/hetzneronline/community-content/master/tutorial-template.md
and I hereby certify that I meet the contribution criteria described in it.
Signed-off-by: YOUR NAME <YOUR@EMAILPROVIDER.TLD>
```

6. If your tutorial is accepted, you will receive an email from a Hetzner Online Community Manager. Please respond to this email, providing your Hetzner Account number, so the reward can be added as a credit to your account.

## Layout
Tutorials should all have the same basic layout:

 * Title
 * Introduction
 * Steps
 * Conclusion

### Title
The title should make clear what the goal of your tutorial is. Don't stuff everything into the title though, this should be a summary that gives users an immediate idea of what the tutorial is about. e.g. Installing `<software>` on `<Operating System>`

### Introduction
The first paragraph or paragraphs are there for you to explain what your tutorial will do. Make sure users know exactly what they will end up with if they follow your tutorial, and let them know if they need any specific prerequisites. You can link to other tutorials that your tutorial builds on, and add recommendations for what users should know.

### Steps
The "Steps" section is the actual set of steps users will take to complete your tutorial. Each step should build on the previous one until the final step finishes the task at hand. It is important not to skip any steps, no matter how obvious or self-explanatory they may seem. Feel free to include screenshots to show exactly what the user should be seeing. The amount of steps will depend entirely on how long/complicated the tutorial is.

### Conclusion
At the end of your tutorial, once the user has completed all the steps, you can add a short conclusion. Summarize what the user has done, and maybe suggest different courses of action they can now take.

## Formatting
The tutorials in the "Hetzner Tutorials" are all written using Markdown. This is a markup language used all over the web. A great overview can be found on Github:
[Markdown-Cheatsheet](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet "Github")
While the title should be a H1 header, all other headers should be H2. If there are two or more smaller steps within a larger step, you can consider making those smaller steps H3.
For specific examples of how to format a tutorial, please take a look at the template below.

## Code Example
You can create code examples in nearly every programming language. Just state the language after the first three backticks in your Markdown file.

```javascript
var s = "JavaScript syntax highlighting";
alert(s);
```
 
```python
s = "Python syntax highlighting"
print s
```

## Terminology
Many tutorials will need to include example usernames, hostnames, domains, and IPs. To simplify this all tutorials should use the same default examples, as outlined below.

* Username: `holu` (short for Hetzner OnLine User)
* Hostname: `<your_host>`
* Domain: `<example.com>`
* IPv4: `<10.0.0.1>`
* IPv6: `<2001:db8:1234::1>`

## Graphic
You are also welcome to send us a graphic for the Introduction area. Graphics should be created in a ratio of 16:9 and be max. 250kb in size.

## Template
To help you get started, we've prepared a template that you can build on. It includes a basic layout for your tutorial, some examples of formatting, and a number of tips and tricks for setting everything up. Please find that here:

[Tutorial Template](tutorial-template.md)

## Submissions
If you think you have a tutorial that meets the criteria above, and would be useful to share, please reach out to us via our github account.
