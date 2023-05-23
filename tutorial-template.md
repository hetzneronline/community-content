---
SPDX-License-Identifier: MIT
path: "/tutorials/tutorial-template"
slug: "tutorial-template"
date: "2023-01-01"
title: "A great Tutorial Template!"
short_description: "This is a tutorial template, including metadata (the first few lines before the actual tutorial). Please fill in as much as possible. If you don't know what to put somewhere, just leave it empty, the Community Manager will fill it for you. Your description should be less than 160 characters."
tags: ["Development", "Lang:Go", "Lang:JS"]
author: "Your Name"
author_link: "https://github.com/....."
author_img: "https://avatars3.githubusercontent.com/u/....."
author_description: "Manipulating arrays of characters in modern text editors that need more RAM than we used to fly to the moon. But it's super awesome..."
language: "en"
available_languages: ["en", "Enter all other available languages of the tutorial using ISO 639-1 codes"]
header_img: "header-x"
cta: "product"
---

## Introduction

The first paragraph or paragraphs are there for you to explain what your tutorial will do. Please don't simply list the steps you will be following, a table of contents (TOC) with the steps will be automatically added.
Make sure users know exactly what they will end up with if they follow your tutorial, and let them know if they need any specific prerequisites.
You can link to other tutorials that your tutorial builds on, and add recommendations for what users should know.

**Prerequisites**

If there are any prerequisites for your tutorial, please write them out here.
If there is already a tutorial explaining one of the prerequisites, make sure to link to that other tutorial.

For example:

* Hetzner Cloud [API token](https://docs.hetzner.com/cloud/api/getting-started/generating-api-token) in the [Cloud Console](https://console.hetzner.cloud/)
* [SSH key](https://community.hetzner.com/tutorials/howto-ssh-key)

**Example terminology**

Many tutorials will need to include example usernames, hostnames, domains, and IPs. To simplify this, all tutorials should use the same default examples, as outlined below.

* Username: `holu` (short for Hetzner OnLine User)
* Hostname: `<your_host>`
* Domain: `<example.com>`
* Subdomain: `<sub.example.com>`
* IP addresses (IPv4 and IPv6):
   * Server: `<10.0.0.1>` and `<2001:db8:1234::1>`
   * Gateway `<192.0.2.254>` and `<2001:db8:1234::ffff>`
   * Client private: `<198.51.100.1>` and `<2001:db8:9abc::1>`
   * Client public: `<203.0.113.1>` and `<2001:db8:5678::1>`

Do **not** use actual IPs in your tutorial.

## Step 1 - Summary of Step

Steps are the actual steps users will be taking to complete your tutorial.

Each step should build on the previous one, until the final step that finishes the tutorial.

It is important not to skip any steps, no matter how obvious or self-explanatory they may seem.

Feel free to include screenshots, to show exactly what the user should be seeing. Please put screenshots in a separate "images" folder.

The amount of steps will depend entirely on how long/complicated the tutorial is.

## Step 2 - Summary of Step

Quick introduction.

Start by...

![Screenshot Description](images/screenshot_description.png)

Then...

Finally...

### Step 2.1 - Summary of Step

You can create code examples in nearly every programming language.
Just state the language after the first three backticks in your Markdown file.

Here is a code example

```javascript
var s = "JavaScript syntax highlighting";
alert(s);
```

### Step 2.2 - Summary of Step

Another code example

```python
s = "Python syntax highlighting"
print s
```

## Step 3 - Summary of Step (Optional)

Instructions for a step that is not necessary to complete the tutorial, but can be helpful.

## Step N - Summary of Step

Yet more instructions.

## Conclusion

A short conclusion summarizing what the user has done, and maybe suggesting different courses of action they can now take.

##### License: MIT

<!--

Contributor's Certificate of Origin

By making a contribution to this project, I certify that:

(a) The contribution was created in whole or in part by me and I have
    the right to submit it under the license indicated in the file; or

(b) The contribution is based upon previous work that, to the best of my
    knowledge, is covered under an appropriate license and I have the
    right under that license to submit that work with modifications,
    whether created in whole or in part by me, under the same license
    (unless I am permitted to submit under a different license), as
    indicated in the file; or

(c) The contribution was provided directly to me by some other person
    who certified (a), (b) or (c) and I have not modified it.

(d) I understand and agree that this project and the contribution are
    public and that a record of the contribution (including all personal
    information I submit with it, including my sign-off) is maintained
    indefinitely and may be redistributed consistent with this project
    or the license(s) involved.

Signed-off-by: [submitter's name and email address here]

-->
