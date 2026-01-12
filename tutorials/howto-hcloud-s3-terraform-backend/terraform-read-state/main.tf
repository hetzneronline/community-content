terraform {
  backend "s3" {
   
    bucket = "terraform-tutorial"
    key    = "terraform-read-state/terraform.tfstate"
    endpoint = "https://fsn1.your-objectstorage.com"
    
    skip_credentials_validation = true
    skip_metadata_api_check     = true
    skip_region_validation      = true
    use_path_style            = true
  }
}

data "terraform_remote_state" "vm_state" {
  backend = "s3"
  config = {
    bucket = "terraform-tutorial"
    key                        = "terraform-backend-tutorial/terraform.tfstate"
    endpoint                   = "https://fsn1.your-objectstorage.com"
    skip_credentials_validation = true
    skip_metadata_api_check     = true
    skip_region_validation      = true
    use_path_style              = true
  }
}


output "vm_id" {
  description = "ID of the provisioned VM"
  value       = data.terraform_remote_state.vm_state.outputs.vm_id
}

output "vm_ipv4" {
  description = "Public IPv4 address of the VM"
  value       = data.terraform_remote_state.vm_state.outputs.vm_ipv4
}

output "vm_name" {
  description = "Name of the VM"
  value       = data.terraform_remote_state.vm_state.outputs.vm_name
}