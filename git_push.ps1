# PowerShell script to push changes to GitHub
$repoPath = "C:\xampp\htdocs\modified-nuxbill"
$githubUrl = "https://github.com/wilndotcom/modified-nuxbill.git"

Set-Location $repoPath

# Configure git
& git config user.email "kennethndugi@gmail.com"
& git config user.name "wilndotcom"

# Add all changes
& git add -A

# Commit with message
& git commit -m "feat: add colorful sidebar theme with beautiful menu design and hover effects for admin and customer portals" --author="wilndotcom <kennethndugi@gmail.com>"

# Push to GitHub
& git push $githubUrl main

Write-Host "Done!"
