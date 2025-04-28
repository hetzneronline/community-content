# An Example Project to demonstrate the use of Django-Storages with Object Storage

## Prerequisites

- A Hetzner Cloud account
- A Hetzner Object Storage bucket in private mode
- A Hetzner Object Storage access key and secret key
- Python 3.12 

## Usage

1. copy `.env.example` to `.env` and fill in the values according to your Hetzner Object Storage bucket:

```bash
cp .env.example .env
```

2. create a virtual environment, activate it, and set Python Cache Prefix to a temporary location to avoid creating `__pycache__` folders in the project directories:

```bash
python3 -m venv venv
source venv/bin/activate
export PYTHONPYCACHEPREFIX=/tmp/pycache
```

3. install the requirements:

```bash
pip install -r requirements.txt
```

4. Change into the folder with `manage.py` and run the migrations:

```bash
cd example_project
python manage.py migrate
```

5. create a superuser:

```bash
python manage.py createsuperuser
```

6. run the server

```bash
python manage.py runserver
```

7. open the admin panel in your browser

```bash
http://localhost:8000/admin/
```

8. login with the superuser credentials and test the file uploads

## Cleanup

1. Cleanup the bucket by deleting the files in the admin panel

2. Stop the server with `ctrl-c` and deactivate the virtual environment:

```bash
deactivate
```

3. Delete the virtual environment, the database, and the `.env` file:

```bash
cd ..
rm -rf venv
rm example_project/db.sqlite3
rm .env
```

#### License: MIT

Author: [Mitja Martini](https://github.com/mitja)


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

Signed-off-by: Mitja Martini <hi@mitjamartini.com>

-->