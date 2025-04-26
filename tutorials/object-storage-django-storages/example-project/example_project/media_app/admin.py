from django.contrib import admin
from django.utils.html import format_html
from .models import PublicDocument, PrivateDocument


@admin.register(PublicDocument)
class PublicDocumentAdmin(admin.ModelAdmin):
    list_display = ('title', 'file_url')
    
    def file_url(self, obj):
        return format_html(
            '<a href="{}" target="_blank">View File</a>', 
            obj.file.url
        )
    file_url.short_description = 'File Link'


@admin.register(PrivateDocument)
class PrivateDocumentAdmin(admin.ModelAdmin):
    list_display = ('title', 'get_file_url')
    readonly_fields = ('get_file_url',)
    
    def get_file_url(self, obj):
        url = obj.get_presigned_url()
        return format_html(
            '<a href="{}" target="_blank"> Download File</a>',
            url
        )
    get_file_url.short_description = 'Download Link'

    def get_fields(self, request, obj=None):
        fields = list(super().get_fields(request, obj))
        # Only show the URL when editing an existing object
        if obj:
            fields.append('get_file_url')
        return fields
    