<template>
    <div class="w-4/6 mx-auto">
        <navigation></navigation> 
        <slot/>
    </div>
</template>

<script>
import Navigation from '@/Components/Navigation.vue';
import { Link } from '@inertiajs/vue3';

export default{
    name: 'MainLayout',

    components: {
        Link,
        Navigation
    }
}

</script>

<style scoped>

</style>