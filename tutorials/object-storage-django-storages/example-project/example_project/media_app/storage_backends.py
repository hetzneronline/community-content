from django.conf import settings
from django.core.files.storage import FileSystemStorage
from storages.backends.s3boto3 import S3Boto3Storage

settings.LOCATION_PREFIX

class BaseMediaStorage(S3Boto3Storage):
    signature_version = "s3"
    file_overwrite = False
    custom_domain = False


class PublicMediaStorage(BaseMediaStorage):
    location = settings.PUBLIC_MEDIA_LOCATION
    default_acl = "public-read"


class PrivateMediaStorage(S3Boto3Storage):
    location = settings.PRIVATE_MEDIA_LOCATION
    default_acl = "private"


def get_private_file_storage():
    if not settings.USE_S3_MEDIA:
        return FileSystemStorage()
    else:
        return PrivateMediaStorage()