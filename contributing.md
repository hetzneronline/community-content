# Hetzner Tutorial Guidelines

Below, you can find information about [content](#content), [language](#language), [external references](#external-references), the [review process](#review-process), [credits](#credits), and a [step by step guide](#how-to-contribute-step-by-step) on how to contribute.

> If you want to contribute a tutorial, you should also check out our [tutorial template](https://github.com/hetzneronline/community-content/blob/master/tutorial-template.md) and our [Markdown preview](https://community.hetzner.com/markdown-test-suite/)!

## General Criteria

### Content

* **Original**
  
  * You cannot re-submit any work that is published somewhere else on the web, even if you are the original author.
  * If you create a pull request for a tutorial that is not original, we will close this pull request and we will not publish the tutorial. In addition, we will not publish any other tutorials submitted by that user. This means that any future pull requests by that user will be closed without review.

* **Topic**
  
  * Before you start writing your tutorial, make sure there isn't already a tutorial at [community.hetzner.com](https://community.hetzner.com/tutorials) about the exact same topic.
  * You might also want to check existing pull requests, just to be safe.

* **Provide value**
  
  * The tutorial should provide value to the reader. Please do not just copy and paste commands and add one-line instructions. Instead, add some explanation where it's needed and provide some additional information if it's relevant.
  * Please do not just write about a product or software itself. Usually, there is already official documentation with that same information. What you can do, however, is write a tutorial to explain how to use a certain product or software in a specific environment.

### Language
 
* **English**
  
  * If you're fluent in another language, and are able to make a translation, you can submit your tutorial in multiple languages, as long as at least one of them is English.<br>
    We support the following languages: English `en`, German `de`, Russian `ru`, Italian `it`, Bulgarian `bg`, Finnish `fi`, and Portuguese `pt`
  * If you're not fluent in English, but you have a great tutorial to share in a different language, please reach out to us. We are open to possibly making exceptions for specific tutorials.

* **Easy to understand**
  
  * These tutorials will be read by users with a wide range of experience. Make sure beginners can still follow what is being done. This means it is important not to skip any steps, no matter how obvious or self-explanatory they may seem. Feel free to include screenshots to show exactly what the user should be seeing.
  * If you use acronyms, make sure to write them out the first time you use them.
  * Don't use excessive jargon or techspeak. Again, if you do use a word that not everybody might understand, either explain it, or use an easier to understand word or phrase.
  * Jokes are allowed, but don't overdo it.

### External references

* **Free and open source**
  
  * If your tutorial includes products or software of a third party, make sure they are free-of-charge and open-source, so that everyone can follow your tutorial.

* **Links, Docker images, Terraform modules**
  
  * If you want to add a link in your tutorial, please make sure it takes the reader to an official and trusted website.
  * If you want to use a Docker image or a Terraform module in your tutorial, make sure it is a trusted one. This means that you should not use a Docker image with only 12 downloads, for example.

* **External repositories**
  
  * Your tutorial should not include links to or content of small external repositories. The reason for this is that we at Hetzner cannot control any changes or deletions made to those small external repositories. As a result, the submitted tutorial might not work anymore.

----------------------------------

## Review process

1. After you create your pull request, one of the Community Managers will evaluate your tutorial and provide you with feedback. 
2. Depending on the feedback, you might need to update your tutorial.
   
   > If you don't respond to the feedback provided by the Community Manager and you stop working on the tutorial, the pull request will be marked as `stale`. If you start working on the tutorial again, we will remove that label. If the tutorial remains without activity for a long period of time, we will close the pull request. Should you find time again to continue working on the tutorial, you are welcome to open a new pull request.

3. Once the Community Manager has no more feedback and the tutorial looks good, the Community Manager will add the `ready` label to your pull request.
4. Someone will then do a final check for spelling or formatting mistakes and publish the tutorial.

If your tutorial is accepted, you will receive an email from a Hetzner Online Community Manager. Please respond to this email and provide your Hetzner Account number, so the reward can be added as a credit to your account.

----------------------------------

## Credits

* **New tutorials**
  
  * New tutorials are rewarded with **up to** €50 credit. The final amount depends on how detailed and in-depth the tutorial is.

* **Updates**
  
  * Minor updates are not rewarded with a credit.
  * Major updates are rewarded with about €10 credit.

* **Requirements**
  
  * In order to receive credits, you need at least one invoice in your Hetzner account.

----------------------------------

## Updates

Since you know your tutorial best, it would be great if you could help us keep your tutorial up-to-date. 

* **Issues**
  
  If someone opens an issue related to your tutorial, we might mention you in it so that you can take a look at it. You can then check whether you'd be interested in helping to solve the issue, or in updating your tutorial if necessary.

----------------------------------
----------------------------------

## How to contribute step by step

1. **[Fork](https://github.com/hetzneronline/community-content/fork) and clone the project**

2. **Add a folder for your tutorial**
   
   ```bash
   cd community-content
   mkdir tutorials/my-tutorial-name
   ```

   > Change `my-tutorial-name` to a unique name. You will have to specify this folder name in the metadata of your tutorial.

   Each tutorial has its own folder. This folder contains everything that is part of the tutorial. If you use images, create a separate folder called `images` within your `my-tutorial-name` folder. You will also need one file called `01.en.md` which contains the tutorial in English language. If you want to add a second language, you will have to add a second file.

   Here is an example of a tutorial that is available in English and German. It also includes some images.

   ![example-tutorial-files](https://raw.githubusercontent.com/hetzneronline/community-content/master/example-tutorial-files.jpg)

3. **Use the template**
   
   To help you get started, we've prepared a [tutorial template](https://github.com/hetzneronline/community-content/blob/master/tutorial-template.md) that you can build on. It includes a basic layout for your tutorial, some examples of formatting, and a number of tips and tricks for setting everything up. You can copy the content of this file to your own tutorial and edit it where needed.

   > If you already have a file called `01.en.md`, this command will overwrite the content of that file.
   
   ```bash
   cp tutorial-template.md tutorials/my-tutorial-name/01.en.md
   ```

   The tutorials at [community.hetzner.com](https://community.hetzner.com/tutorials) are all written using Markdown. This is a markup language used all over the web. You can find a great overview on GitHub:
   [Markdown-Cheatsheet](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet "GitHub")
  
   The title you add at the top in `title: "<your-title>"` will automatically be added as a H1 headline at the beginning of your tutorial, so you do **not** need to add the title again below the metadata yourself. Instead, you can start directly with your introduction.
   
   ```markdown
   ---
   SPDX-License-Identifier: MIT
   path: "/tutorials/my-tutorial-name"
   slug: "my-tutorial-name"
   title: "Installing <software> on Ubuntu"
   short_description: "This description should be less than 160 characters."
   ...
   ---
   
   ## Introduction
   ```
   
   Apart from the title, all other headers should be H2. If there are two or more smaller steps within a larger step, you can consider making those smaller steps H3.
   For specific examples of how to format a tutorial, please take a look at the [tutorial template](https://github.com/hetzneronline/community-content/blob/master/tutorial-template.md).
   
   When you edit the tutorial template, you should also note the following:

<ul><ul>

<details>

<summary>Tutorial metadata</summary>

* **"title"**

  * The title should make clear what the goal of your tutorial is. Don't stuff everything into the title, though; it should be a summary that gives users an immediate idea of what the tutorial is about. e.g. "Installing `<software>` on `<operating system>`"

* **"short_description"**
     
  * No more than 160 characters.

* **"tags"**
     
  <u>OS images, such as:</u>
  * Ubuntu
  * Fedora
  * or others
 
  <u>Hetzner tools, such as:</u>
  * `hcloud-cli`
  * `installimage`
  * or others
   
  Software that is used in the tutorial<br>
  Or other labels

------

</details>

<details>

<summary>Prerequisites</summary>

**Server**

<ul><li>If your tutorial requires a server, the instructions should work on a new server.</li>
<li>If a user just ordered a server, they should be able to follow the tutorial step by step, without having to install or configure anything first. If there are prerequisites for your tutorial, though, please make sure there is already a tutorial explaining that. You should then link this tutorial at the beginning of your own tutorial.</li></ul>

------

</details>

</ul></ul>

4. **Write the content**
   
   When you write your content, make sure it meets the General Criteria presented at the top.

   You can use our [Markdown preview](https://community.hetzner.com/markdown-test-suite/) to see how your tutorial will look after it was published and to check your text for any formatting mistakes.

   Also make sure that your tutorial includes the [license block](https://github.com/hetzneronline/community-content/blob/master/tutorial-template.md?plain=1#L108-L137) at the bottom of the tutorial file. And remember to replace `[submitter's name and email address here]` with your own name and email address.

5. **Commit and push your tutorial**

   Create a git branch for your tutorial:
   
   ```bash
   git checkout -b my-tutorial
   ```
   
   > Replace `my-tutorial` with a short name that describes your tutorial.

   Commit your changes to the new branch and push your tutorial to GitHub.

6. **Create a pull request**
   
   Create a new pull request [on GitHub](https://github.com/hetzneronline/community-content). In your pull request, you should include the following statement:
   
   ```text
   I have read and understood the Contributor's Certificate of Origin available at the end of 
   https://raw.githubusercontent.com/hetzneronline/community-content/master/tutorial-template.md
   and I hereby certify that I meet the contribution criteria described in it.
   Signed-off-by: YOUR NAME <YOUR@EMAILPROVIDER.TLD>
   ```

One of the Community Managers will evaluate your tutorial and provide you with feedback. If everything is fine, we will publish your tutorial.
