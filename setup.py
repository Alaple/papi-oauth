from setuptools import setup, find_packages

VERSION = '0.1.0'
DESCRIPTION = 'Papi oauth examples'
LONG_DESCRIPTION = 'OAuth authentication examples in different programming languages.'

# Setting up
setup(
  name='papi-oauth',
  version=VERSION,
  author='Alaple (Lucas Palacios)',
  author_email='<sluzquinosa@uni.pe>',
  description=DESCRIPTION,
  long_description_content_type='text/markdown',
  long_description=LONG_DESCRIPTION,
  packages=find_packages(),
  install_requires=['fastapi', 'uvicorn', 'requests', 'jinja2'],
  keywords=['auth'],
  project_urls={
    'Source Code': 'https://github.com/Alaple/papi-oauth'
  },
  entry_points={
    'console_scripts': [
      'py-test=src.index:create_scan_example'
    ]
  },
  classifiers=[
    'Development Status :: 1 - Planning',
    'Intended Audience :: Developers',
    'Programming Language :: Python :: 3',
    'Operating System :: Unix',
    'Operating System :: MacOS :: MacOS X',
    'Operating System :: Microsoft :: Windows'
  ]
)