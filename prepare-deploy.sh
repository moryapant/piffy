#!/bin/bash

echo "🚀 Preparing deployment for Fappify.in..."

# Ensure we have the latest changes
echo "📦 Building production assets..."
yarn build

if [ $? -ne 0 ]; then
    echo "❌ Build failed! Please fix errors before deploying."
    exit 1
fi

echo "✅ Assets built successfully!"

# Show built files
echo "📁 Built assets:"
ls -la public/build/assets/ | head -10

echo ""
echo "🎯 Next steps:"
echo "1. Commit and push any code changes"
echo "2. Upload the public/build/ directory to your server"
echo "3. Run deployment script on server"
echo ""
echo "Or if your server supports git deployment:"
echo "git add public/build/ -f"
echo "git commit -m 'Add built assets for deployment'"
echo "git push origin main"
echo "Then run ./shared-hosting-deploy.sh deploy on server"