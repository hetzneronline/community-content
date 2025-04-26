import os
import uuid

from django.conf import settings
from django.core.exceptions import ValidationError
from django.db import models

from .storage_backends import get_private_file_storage, PrivateMediaStorage


def _get_random_filename(instance, filename):
    model_name = instance.__class__.__name__.lower()
    ext = filename.split('.')[-1]
    new_filename = f"{uuid.uuid4()}.{ext}"
    return os.path.join(model_name, new_filename)


class PublicDocument(models.Model):
    title = models.CharField(max_length=255)
    file = models.FileField(upload_to=_get_random_filename)


class PrivateDocument(models.Model):
    title = models.CharField(max_length=255)
    file = models.FileField(
        upload_to=_get_random_filename,
        storage=get_private_file_storage
    )

    def get_presigned_url(self):
        if settings.USE_S3_MEDIA:
            storage = PrivateMediaStorage()
            return storage.url(
                self.file.name, 
                expire=settings.PRESIGNED_URL_EXPIRATION
            )
        return None
