**Example header**
All tutorials must have the following header lines filled in. This metadata is used by the community to categorise and describe the tutorial.

---
title: ‘A great example tutorial!’
SPDX-License-Identifier: MIT
path: ‘/tutorials/tutorial-template/en’
slug: ‘tutorial-template’
date: ‘2025-01-01’
title: ‘A great example tutorial!’
short_description: ‘This is a sample tutorial with metadata (the first few lines before the actual tutorial). Please fill in as much as you can yourself. If you are unsure about something, you can leave it blank and the community manager will adjust it for you. The “short_description” should not exceed 160 characters.’
tags: [‘Development’, ‘Lang:Go’, ‘Lang:JS’]
author: ‘Your name’
author_link: ‘https://github.com/.....’
author_img: ‘https://avatars3.githubusercontent.com/u/.....’
author_description: ‘Short description about yourself.’
language: ‘en’
available_languages: [‘en’, “de”, ‘Add all languages (ISO-639-1 codes) in which the tutorial is available here’]
header_img: ‘header-x’
cta: ‘product’
---


**Example names**
Many tutorials need to include user names, host names, domains, and IPs, for example. To simplify this, all tutorials should use the same standard examples as described below.
* User name: `holu` (abbreviation for Hetzner OnLine User)
* Host name: `<your_host>`
* Domain: `<example.com>`
* Subdomain: `<sub.example.com>`
* IP addresses (IPv4 and IPv6):
    * Server: `<10.0.0.1>` and `<2001:db8:1234::1>` 
    * Gateway `<192.0.2.254>` and `<2001:db8:1234::ffff>`
    * Client private: `<198.51.100.1>` and `<2001:db8:9abc::1>`   
    * Client public: `<203.0.113.1>` and `<2001:db8:5678::1>`

Never use real IP addresses in your tutorial.

**Formatting**
Tutorials should be written in Markdown. Markdown is a simple markup language that is easy to read and write.
The introduction should always be written in size #### and the subheadings in size #####. (See example below)

## Introduction (e.g. What is ...?)

Please note that all tutorials must be written in English. If you would like to provide a German translation as well, you can use this sample tutorial as a template.
The first paragraph or paragraphs in the introduction are there to explain what the tutorial covers. Please do not simply list the individual steps, as a table of contents is added automatically. Make sure that users know exactly what they will achieve at the end if they follow your tutorial. Let them know if they need to meet certain prerequisites.
You can refer to other tutorials on which your tutorial is based and add recommendations about what users should know.

## Prerequisites

If your tutorial can only be used if certain prerequisites are met, these should be specified here.
If there is already a tutorial that explains one of the prerequisites, it should be linked.
For example:
* Hetzner Cloud [API token](https://docs.hetzner.com/de/cloud/api/getting-started/generating-api-token) in the [Cloud Console](https://console.hetzner.cloud/)
* [SSH key](https://community.hetzner.com/tutorials/howto-ssh-key/de)

## Step 1 (e.g. download ...)

The steps are the actual steps that users will perform to complete the tutorial.
Each step should build on the previous one, up to the last step, which completes the tutorial.
It is important not to skip any steps, no matter how obvious or self-explanatory they may seem.
Feel free to add screenshots to show exactly what the user should see. Place all screenshots in a separate `images` folder.
The number of steps depends entirely on how long/complicated the tutorial is.

## Step 2 (e.g. install ...)

Brief introduction. (e.g. you can upload ... to your web space in two ways)
e.g. Option 1...
![Screenshot Description](images/screenshot_description.png)

e.g. Option 2...
1.
2.
3.
Finally...

### Step 2.1 (e.g. present a code example or several possibilities)

You can create code examples in almost any programming language.
Simply specify the language after the first three backticks in the Markdown file.
Here is a code example

```javascript
var s = ‘JavaScript syntax highlighting’;
alert(s);
```

### Step 2.2 (e.g. another code example)

```python
s = ‘Python syntax highlighting’
print s
```

## Step N (e.g. Import settings ....)
More instructions.
**Result**
At the end of the tutorial, once the user has completed all the steps, you can add a short conclusion. Summarise what the user has done and perhaps suggest various actions they can take now.

## (Optional) Next steps (links to further/interesting tutorials or support links)

#### Licence: MIT
<!--
Contributor's Certificate of Origin
By making a contribution to this project, I certify that:
(a) The contribution was created in whole or in part by me and I have
    the right to submit it under the licence indicated in the file; or
(b) The contribution is based upon previous work that, to the best of my
    knowledge, is covered under an appropriate licence and I have the
    
right under that licence to submit that work with modifications,
    whether created in whole or in part by me, under the same licence
    (unless I am permitted to submit under a different licence), as
    indicated in the file; or
(c) The contribution was provided directly to me by some other person
    who certified (a), (b) or (c) and I have not modified it.
(d) I understand and agree that this project and the contribution are
public and that a record of the contribution (including all personal
information I submit with it, including my sign-off) is maintained
indefinitely and may be redistributed consistent with this project
or the licence(s) involved.
Signed-off-by: [submitter's name and email address here]
-->