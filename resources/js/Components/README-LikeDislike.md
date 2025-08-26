# Like/Dislike Module System

A comprehensive, reusable voting system for the fappify application that supports various layouts, sizes, and use cases.

## 🎯 Overview

The Like/Dislike Module System provides a consistent, Reddit-style voting interface that can be used across all pages of your application. It matches the design shown in your reference image with circular buttons and clean layouts.

## 📦 Components

### 1. `LikeDislikeModule.vue` (Base Component)
The main reusable component that handles all voting functionality.

### 2. `VoteButtonVertical.vue` (Vertical Layout)
Pre-configured for vertical layouts (like Reddit's left sidebar on posts).

### 3. `VoteButtonHorizontal.vue` (Horizontal Layout)
Pre-configured for horizontal layouts (like inline with comments or mobile).

## 🚀 Usage Examples

### Basic Usage

```vue
<script setup>
import LikeDislikeModule from '@/Components/LikeDislikeModule.vue'

const handleVote = (contentId, voteType) => {
  // Your vote handling logic
  console.log(`Voting ${voteType} on content ${contentId}`)
}
</script>

<template>
  <LikeDislikeModule 
    :content="post"
    @vote="handleVote"
  />
</template>
```

### Vertical Layout (Reddit-style)

```vue
<script setup>
import VoteButtonVertical from '@/Components/VoteButtonVertical.vue'
</script>

<template>
  <!-- Large size for main post pages -->
  <VoteButtonVertical 
    :content="post"
    size="large"
    @vote="handleVote"
  />
  
  <!-- Medium size for post listings -->
  <VoteButtonVertical 
    :content="post"
    size="medium"
    @vote="handleVote"
  />
</template>
```

### Horizontal Layout (Inline)

```vue
<script setup>
import VoteButtonHorizontal from '@/Components/VoteButtonHorizontal.vue'
</script>

<template>
  <!-- Compact horizontal for comments -->
  <VoteButtonHorizontal 
    :content="comment"
    size="small"
    :compact="true"
    @vote="handleVote"
  />
  
  <!-- Full horizontal for post actions -->
  <VoteButtonHorizontal 
    :content="post"
    size="medium"
    @vote="handleVote"
  />
</template>
```

## 🎛️ Configuration Options

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `content` | Object | **Required** | The content object with voting data |
| `size` | String | `'medium'` | Size variant: `'small'`, `'medium'`, `'large'` |
| `layout` | String | `'vertical'` | Layout: `'vertical'`, `'horizontal'` |
| `showCount` | Boolean | `true` | Show vote count |
| `compact` | Boolean | `false` | Compact mode (minimal styling) |
| `contentType` | String | `'post'` | Content type for analytics |

### Content Object Structure

Your content object should have these properties:

```javascript
{
  id: 123,
  upvotes: 42,      // Optional: number of upvotes
  downvotes: 3,     // Optional: number of downvotes
  score: 39,        // Optional: calculated score (upvotes - downvotes)
  user_vote: {      // Optional: current user's vote
    vote_type: 1    // 1 for upvote, -1 for downvote, null for no vote
  }
}
```

### Size Variants

#### Small (`size="small"`)
- Button: 24x24px (w-6 h-6)
- Icon: 12x12px (w-3 h-3)
- Text: text-xs
- **Use for**: Comments, compact areas, mobile menus

#### Medium (`size="medium"`) - Default
- Button: 32x32px (w-8 h-8)
- Icon: 16x16px (w-4 h-4)
- Text: text-sm
- **Use for**: Post listings, general content

#### Large (`size="large"`)
- Button: 48x48px (w-12 h-12)
- Icon: 24x24px (w-6 h-6)
- Text: text-base
- **Use for**: Main post pages, featured content

## 🎨 Visual Design

The components use the classic thumbs up/down design that matches your site's theme:
- **Active Upvote**: Blue background (#3B82F6) with white thumbs-up icon
- **Active Downvote**: Red background (#EF4444) with white thumbs-down icon  
- **Inactive**: Gray background with gray thumbs icons
- **Hover Effects**: Color transitions and subtle scaling
- **Pulse Animation**: Active votes have a subtle glow effect
- **Icons**: Original thumbs up/down design for familiarity and clarity

## 📱 Responsive Behavior

The components automatically adapt to different screen sizes:
- **Desktop**: Vertical layout with larger buttons
- **Mobile**: Horizontal layout with smaller buttons
- **Tablets**: Medium size with adaptive layouts

## 🔧 Implementation Status

### ✅ Completed Pages
- **Welcome Page**: Uses vertical layout in Reddit-style post cards
- **Post Show Page**: Large vertical for desktop, small horizontal for mobile
- **Subfapp Show Page**: Small horizontal in post action bars

### 🎯 Recommended Usage by Page

| Page Type | Recommended Component | Size | Notes |
|-----------|----------------------|------|-------|
| Home/Welcome | `VoteButtonVertical` | `medium` | In post listing cards |
| Post Detail | `VoteButtonVertical` | `large` | Main post voting |
| Post Detail Mobile | `VoteButtonHorizontal` | `small` | Mobile action bar |
| Community Pages | `VoteButtonHorizontal` | `small` | Post summaries |
| Comments | `LikeDislikeModule` | `small` | Inline with comments |
| Sidebars | `VoteButtonVertical` | `small` | Quick vote widgets |

## 🔄 Events

### `@vote` Event
Emitted when user clicks like/dislike buttons.

```javascript
// Event payload
{
  contentId: 123,    // ID of the content being voted on
  voteType: 1        // 1 for upvote, -1 for downvote, 0 for removing vote
}
```

### Vote Handler Example

```javascript
const handleVote = (contentId, voteType) => {
  form.vote_type = voteType
  form.post(route('posts.vote', contentId), {
    preserveScroll: true,
    onSuccess: () => {
      // Handle successful vote
    }
  })
}
```

## 🎭 Animation Features

- **Hover Effects**: Smooth color transitions and button scaling
- **Active State**: Pulse glow animation for voted items
- **Click Feedback**: Scale-down effect on button press
- **Icon Rotation**: Downvote icon rotates 180° for visual clarity

## 🔍 Accessibility

- **ARIA Labels**: Proper button titles and labels
- **Keyboard Navigation**: Full keyboard support
- **Focus Indicators**: Visible focus rings
- **Screen Readers**: Semantic markup for vote counts

## 🐛 Troubleshooting

### Common Issues

1. **Vote count not updating**: Ensure your content object has the correct structure
2. **Styling conflicts**: Check that Tailwind classes aren't being overridden
3. **Event not firing**: Verify the `@vote` handler is correctly implemented

### Debug Mode

Add this to see vote events in console:

```vue
<LikeDislikeModule 
  :content="post"
  @vote="(id, type) => console.log('Vote:', id, type)"
/>
```

## 🎉 Future Enhancements

- **Themes**: Support for custom color schemes
- **Animations**: More elaborate vote animations
- **Sounds**: Optional click sounds
- **Analytics**: Built-in vote tracking
- **Reactions**: Extended emoji reaction system